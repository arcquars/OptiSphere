<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Models\User;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseIncome;
use App\Models\WarehouseRefund;
use App\Models\WarehouseStockHistory;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
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
                // Aquí defines las columnas de tu tabla basadas en los datos de $this->warehouseM
                // Por ejemplo:
                TextColumn::make('id')->label('ID'),
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
                    // Deshabilitamos el comportamiento por defecto del formulario de Filament
                    ->form([]) 
                    ->disabled(fn (WarehouseStockHistory $record) => 
                        $record->movement_type !== "INGRESO" 
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
}
