<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Models\InventoryMovement;
use App\Models\ProductStock;
use App\Models\User;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseIncome;
use App\Models\WarehouseRefund;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use App\Rules\CheckBranchSendProducts;
use App\Rules\CheckSendProducts;
use App\Services\WarehouseStockHistoriService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

// use Filament\Resources\Pages\Page;

class HistoryMovementShow extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static string $resource = WarehouseResource::class;
    
    protected string $view = 'filament.resources.warehouses.pages.history-movement-show';

    public int $history_id;
    public $action = "";
    public string $bgAction;

    public $userM;

    public $warehouseM;

    public $selectedBranchId;

    // Sucursal destino para el traslado sucursal -> sucursal (contexto ENTREGA)
    public $destinationBranchId;

    protected static ?string $title = 'Ver Historial de Movimiento';
    public function mount(int $history_id, string $action): void
    {
        if(strcmp($action, "ENTREGA_SUCURSAL") === 0){
            $action = "ENTREGA";
        }
        $this->history_id = $history_id;
        $this->action = $action;
        $this->warehouseM = null;
        
        switch($action){
            case "INGRESO":
                $this->warehouseM = WarehouseIncome::find($history_id);
                $this->bgAction = "success";
                break;
            case "ENTREGA":
                $this->warehouseM = WarehouseDelivery::find($history_id);
                $this->bgAction = "info";
                break;
            default:
                $this->warehouseM = WarehouseRefund::find($history_id);
                $this->bgAction = "warning";
                break;

        }
        if($this->warehouseM->base_code != null){
            $wshs = WarehouseStockHistory::where('movement_type', 'like', "%".$this->action."%")
                ->where('type_id', $this->warehouseM->id) // Asumiendo que usas las propiedades de tu clase
                ->with(['warehouseStock', 'warehouseStock.product', 'warehouseStock.product.opticalProperties'])
                ->first();
            if($wshs->warehouseStock->product->opticalProperties->type){
                $url = WarehouseResource::getUrl('history.show', [
                    "history_id" => $this->history_id, 
                    "action" => $this->action, 
                    "type" => $wshs->warehouseStock->product->opticalProperties->type, 
                    "code" => $wshs->warehouseStock->product->opticalProperties->base_code
                ]);
                redirect()->to($url);
            }
        }
        $this->userM = User::find($this->warehouseM->user_id);
        $this->action = $action;
        // dd($this->history_id, $this->action);
    }

    protected function rules()
    {
        return [
            'selectedBranchId' => ['required', 'exists:branches,id', new CheckSendProducts($this->warehouseM->id, $this->action)],
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // enviar $this->warehouseM with withCount de warehouseStockHistory para mostrar el conteo de productos relacionados
                WarehouseStockHistory::where('movement_type', 'like', "%".$this->action."%")
                    ->where('type_id', $this->warehouseM->id) // Asumiendo que usas las propiedades de tu clase
                    ->with(['warehouseStock', 'warehouseStock.product', 'warehouseStock.product.opticalProperties'])
            )
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('warehouseStock.product.code')->label('Codigo')->formatStateUsing(function ($state, $record) {
                    return $record->warehouseStock->product->code ?? 'N/A';
                }),
                TextColumn::make('warehouseStock.product')->label('Producto')->formatStateUsing(function ($state, $record) {
                    return $record->warehouseStock->product->name ?? 'N/A';
                }),
                TextColumn::make('old_quantity')
                    ->label('Cantidad Anterior')
                    ->alignEnd()
                    ->numeric(),
                TextColumn::make('difference')
                    ->label('Diferencia')
                    ->alignEnd()
                    ->numeric(),
                TextColumn::make('new_quantity')
                    ->label('Cantidad Nueva')
                    ->alignEnd()
                    ->numeric(),
                
                // Column::make('created_at')->label('Fecha'),
                // Column::make('quantity')->label('Cantidad'),
                // etc.
             ])
             ->filters([
                 //
             ])
             ->recordActions([
                Action::make('updateDiference')
                    ->label("Actualizar")
                    ->icon('heroicon-m-pencil-square')
                    // Deshabilitamos el comportamiento por defecto del formulario de Filament
                    ->form([]) 
                    ->disabled(fn (WarehouseStockHistory $record) => 
                        !in_array($record->movement_type, [
                            WarehouseStockHistory::MOVEMENT_TYPE_INCOME,
                            WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY,
                        ])
                        || strcmp($record->warehouse_m->status, WarehouseIncome::STATUS_VOID) == 0
                    )
                    // Aquí inyectamos mágicamente tu componente Livewire dentro del cuerpo del modal
                    ->modalContent(fn (WarehouseStockHistory $record): View => view(
                        'filament.resources.warehouses.pages.partials.edit-stock-modal-container',
                        ['record' => $record]
                    ))
                    // Ocultamos los botones por defecto de "Aceptar" y "Cancelar" de Filament 
                    // para que tu propio componente maneje sus botones de guardar/cerrar
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
             ]);
    }

    // Define el listener en una función limpia y separada
    #[On('history_movement-show-updated')]
    public function refreshMovementHistoryTable(): void
    {
        // Esto fuerza a Livewire a re-renderizar los componentes de la vista, incluyendo la tabla
        $this->render(); 
    }

    public function sendToBranch()
    {
        $this->validate();

        $branchId = $this->selectedBranchId;
        $warehouseStockHistories = WarehouseStockHistory::where('movement_type', $this->action)
            ->where('type_id', $this->warehouseM->id)->get();
        $warehouseMid = $this->warehouseM->warehouse_id;

        DB::transaction(function () use ($warehouseStockHistories, $branchId, $warehouseMid) {
            $warehouseDelivery = WarehouseDelivery::create([
                'warehouse_id' => $warehouseMid,
                'branch_id' => $branchId,
                'user_id' => Auth::id(),
                'delivery_date' => Carbon::now()
            ]);

            foreach ($warehouseStockHistories as $data) {
                // 'id' es el ID de la tabla 'product_stocks'.
                $stockId = $data->warehouseStock->product_id;

                // 'amount' es la nueva cantidad que viene del input.
                $amount = (int) $data->difference;

                Log::info("xxxx:: ", [
                    "warehouse_stock_id" => $data->warehouse_stock_id,
                    "amount" => $amount
                ]);

                $attributes = [
                    'product_id' => $stockId,
                    'warehouse_id' => $warehouseMid, // Ejemplo: asume que es el almacén 1
                ];
                // Buscar el registro de stock por su ID.
                $warehouseStock = WarehouseStock::firstOrCreate($attributes, [
                    'quantity' => 0 // Inicializa la cantidad en 0 si es un nuevo registro
                ]);;

                $oldQuantity = $warehouseStock->quantity;
                $newQuantity = $oldQuantity - $amount;

                $warehouseStock->increment('quantity', ($amount*(-1)));

                if ($amount != 0) {
                    WarehouseStockHistory::create([
                        'warehouse_stock_id' => $warehouseStock->id,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'difference' => $amount,
                        'movement_type' => WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY,
                        'type_id' => $warehouseDelivery->id
                    ]);
                }

                // Buscar registro de stock en sucursal
                $attrProd = [
                    'product_id' => $stockId,
                    'branch_id' => $branchId, // Ejemplo: asume que es el almacén 1
                ];
                $productStock = ProductStock::firstOrCreate($attrProd, [
                    'quantity' => 0 // Inicializa la cantidad en 0 si es un nuevo registro
                ]);;

                $oldQuantity = $productStock->quantity;
                $newQuantity = $oldQuantity + $amount;

                $productStock->increment('quantity', $amount);

                if ($amount != 0) {
                    InventoryMovement::create([
                        'product_id' => $stockId,
                        'from_location_type' => InventoryMovement::LOCATION_TYPE_warehouse,
                        'from_location_id' => $warehouseMid,
                        'to_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                        'to_location_id' => $branchId,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'difference' => $amount,
                        'type' => WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY,
                        'type_id' => $warehouseDelivery->id,
                        'user_id' => Auth::id(),
                    ]);
                }
            }
        });
        // Aquí iría tu lógica de negocio para registrar el envío
        // p.ej. WarehouseDelivery::create([...]);

        $this->selectedBranchId = null;
        $this->dispatch('close-modal', id: 'send-to-branch-modal');
        
        \Filament\Notifications\Notification::make()
            ->title('Envío registrado con éxito')
            ->success()
            ->send();
    }

    /**
     * Traslada los productos de esta ENTREGA hacia otra sucursal.
     * La sucursal origen es la del registro actual ($warehouseM->branch_id).
     */
    public function transferToBranch(WarehouseStockHistoriService $service): void
    {
        // Validar destino y que la sucursal origen tenga stock suficiente
        $this->validate(
            [
                'destinationBranchId' => ['required', 'exists:branches,id', new CheckBranchSendProducts($this->warehouseM->id)],
            ],
            [
                'destinationBranchId.required' => 'Debes seleccionar una sucursal destino.',
                'destinationBranchId.exists'   => 'La sucursal seleccionada no es válida.',
            ]
        );

        // La sucursal destino no puede ser la misma que la de origen
        if ((int) $this->destinationBranchId === (int) $this->warehouseM->branch_id) {
            $this->addError('destinationBranchId', 'La sucursal destino debe ser diferente a la sucursal origen.');
            return;
        }

        try {
            $service->transferBranchToBranch($this->warehouseM->id, (int) $this->destinationBranchId);
        } catch (\Throwable $e) {
            Log::error('Error al trasladar entre sucursales: ' . $e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Error al realizar el traslado')
                ->body($e->getMessage())
                ->danger()
                ->send();
            return;
        }

        $this->destinationBranchId = null;
        $this->dispatch('close-modal', id: 'transfer-to-branch-modal');

        \Filament\Notifications\Notification::make()
            ->title('Traslado entre sucursales registrado con éxito')
            ->success()
            ->send();
    }
}