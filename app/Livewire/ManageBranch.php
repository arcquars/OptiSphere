<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\OpticalProperty;
use App\Models\ProductStock;
use App\Models\WarehouseIncome;
use App\Models\WarehouseRefund;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;
use \App\Models\Warehouse;

class ManageBranch extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public $branchId;
    public $warehouses;
    public $branches;

//    #[Validate('required')]
    public $type;
    public $baseCode;
    public $action = "";

    public $matrix = [];
    public $uniqueSpheres = [];
    public $uniqueCylinders = [];

    public function mount($branchId): void
    {
        $this->branchId = $branchId;
        $this->warehouses = Warehouse::where('is_active', "=",true)->get();
        $this->branches = Branch::where('is_active', "=", true)->where('id', '<>', $branchId)->get();
        $this->form->fill();
    }

    public function loadCylinders(){
        if($this->baseCode){
            $this->matrix = [];
            $opticalProperties = OpticalProperty::where('base_code', $this->baseCode)
                ->where('type', $this->type? "+" : "-")
                ->whereHas('product', function ($query){
                    $query->where('is_active', true);
                })
                ->get();

            $this->uniqueSpheres = $opticalProperties->pluck('sphere')->unique()->sort()->values();
            $this->uniqueCylinders = $opticalProperties->pluck('cylinder')->unique()->sort()->values();

            foreach ($this->uniqueSpheres as $sphere){
                $row = [];
                foreach ($this->uniqueCylinders as $cylinder){
                    $type = $this->type? '+' : '-';
                    /** @var OpticalProperty $op */
                    $op = OpticalProperty::where('base_code', $this->baseCode)
                        ->where('type', $type)
                        ->where('sphere', $sphere)
                        ->where('cylinder', $cylinder)
                        ->first();

                    if(strcmp($this->action, "saldo") == 0 || strcmp($this->action, "devolucion") == 0) {
                        $row[] = [
                            'id' => $op->product_id,
                            'type' => $op->type,
                            'sphere' => $op->sphere,
                            'cylinder' => $op->cylinder,
                            'amount' => $op->product->stockByBranch($this->branchId)];
//                            'amount' => 0];
                    } else
                        $row[] = ['id' => $op->id, 'type' => $op->type, 'sphere' => $op->sphere, 'cylinder' => $op->cylinder, 'amount' => null ];
                }
                $this->matrix[] = $row;
            }
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns([
                        'sm' => 3,
                        'xl' => 3,
                        '2xl' => 3,
                    ])
                    ->schema([
                        Select::make('baseCode')
                            ->label('Codigo base')
                            ->options(OpticalProperty::groupBy('base_code')->pluck('base_code', 'base_code'))
                            ->searchable()
                            ->required(),
                        Select::make('action')
                            ->options([
                                'saldo' => 'Saldo',
                                'devolucion' => 'Devolucion',
//                                'price-normal' => 'Precio normal',
//                                'price-especial' => 'Precio especial',
//                                'price-mayorista' => 'Precio mayorista',
                            ])
                            ->required(),
                        ToggleButtons::make('type')
                            ->label('Positivo/Negativo?')
                            ->boolean(falseLabel: '', trueLabel: '')
                            ->icons([
                                true => 'fas-plus',
                                false => 'fas-minus',
                            ])
                            ->colors([
                                true => 'primary',
                                false => 'danger',
                            ])
                            ->required()
                            ->default(true)
                            ->inline()
                    ])
            ])->statePath('data');
    }


    public function loadMatrixProduct(): void
    {
        $validatedData = $this->form->getState();
        $this->baseCode = $this->form->getState()['baseCode'];
        $this->action = $this->form->getState()['action'];
        $this->type = $this->form->getState()['type'];

        $this->loadCylinders();
        $this->dispatch('clear-markedcells');
    }

    public function save($celdas, $warehouseId = null){
        switch ($this->action){
            case "devolucion":
                $this->saveRefund($celdas, $warehouseId);
                break;
        }
        $this->loadCylinders();
        Notification::make()
            ->title('Éxito')
            ->body('El registro se ha guardado correctamente.')
            ->success()
            ->send();
        $this->dispatch('clear-markedcells');
    }

    public function saveRefund($celdas, $warehouseId){
        $string_array = explode("-", $warehouseId);
        $id = isset($string_array[1])? $string_array[1] : null;
        if($id == null){
            throw new \Exception('El formato de envio de id es incorrecto');
        }

        $posicion = strpos($warehouseId, "warehouse");
        if($posicion !== false){
            // es id de almacen
            $this->saveRefundWarehouse($celdas, $id);
        } else {
            // es id de sucursal
            $this->saveRefundBranch($celdas, $id);
        }

    }

    public function saveRefundWarehouse($celdas, $warehouseId){
        DB::transaction(function () use ($celdas, $warehouseId) {
            $warehouseRefund = WarehouseRefund::create([
                'warehouse_id' => $warehouseId,
                'branch_id' => $this->branchId,
                'user_id' => Auth::id(),
                'refund_date' => Carbon::now()
            ]);

            foreach ($celdas as $data) {
                // 'id' es el ID de la tabla 'product_stocks'.
                $stockId = $data['id'];
                // 'amount' es la nueva cantidad que viene del input.
                $amount = (int) $data['amount'];

                $attributes = [
                    'product_id' => $stockId,
                    'warehouse_id' => $warehouseId,
                ];
                // Buscar el registro de stock por su ID.
                $warehouseStock = WarehouseStock::firstOrCreate($attributes, [
                    'quantity' => 0 // Inicializa la cantidad en 0 si es un nuevo registro
                ]);;

                $oldQuantity = $warehouseStock->quantity;
                $newQuantity = $oldQuantity + $amount;

                $warehouseStock->increment('quantity', $amount);

                if ($amount != 0) {
                    WarehouseStockHistory::create([
                        'warehouse_stock_id' => $warehouseStock->id,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'difference' => $amount,
                        'movement_type' => WarehouseStockHistory::MOVEMENT_TYPE_REFUND,
                        'type_id' => $warehouseRefund->id
                    ]);
                }


                // Buscar registro de stock en sucursal
                $attrProd = [
                    'product_id' => $stockId,
                    'branch_id' => $this->branchId, // Ejemplo: asume que es el almacén 1
                ];
                $productStock = ProductStock::firstOrCreate($attrProd, [
                    'quantity' => 0 // Inicializa la cantidad en 0 si es un nuevo registro
                ]);;

                $oldQuantity = $productStock->quantity;
                $newQuantity = $oldQuantity - $amount;

                $productStock->increment('quantity', ($amount*(-1)));

                if ($amount != 0) {
                    InventoryMovement::create([
                        'product_id' => $stockId,
                        'from_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                        'from_location_id' => $this->branchId,
                        'to_location_type' => InventoryMovement::LOCATION_TYPE_warehouse,
                        'to_location_id' => $warehouseId,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'difference' => $amount,
                        'type' => WarehouseStockHistory::MOVEMENT_TYPE_REFUND,
                        'user_id' => Auth::id(),
                    ]);
                }

            }
        });
    }

    public function saveRefundBranch($celdas, $branchId){
        try {
            DB::beginTransaction(); // Inicia la transacción

            foreach ($celdas as $data) {
                // Validación de datos y tipado
                $stockId = $data['id'];
                $amount = (int) $data['amount'];

                // 1. GESTIÓN DEL STOCK EN SUCURSAL DE ORIGEN (QUIEN DEVUELVE)
                // -----------------------------------------------------------------
                $attrProdOrigen = [
                    'product_id' => $stockId,
                    'branch_id' => $this->branchId,
                ];


                $productStockOrigen = ProductStock::firstOrCreate($attrProdOrigen, ['quantity' => 0]);

                $oldQuantityOrigen = $productStockOrigen->quantity;
                $newQuantityOrigen = $oldQuantityOrigen - $amount;

                // Bloquear el registro para asegurar atomicidad (opcional, pero recomendado)
                // $productStockOrigen->lockForUpdate();

                if ($newQuantityOrigen < 0) {
                    // Si la cantidad resultante es negativa, lanzar una excepción y forzar ROLLBACK
                    throw new \Exception("Stock insuficiente para el producto ID {$stockId} en sucursal {$this->branchId}.");
                }

                // Usar la funcionalidad de incremento para una operación atómica
                $productStockOrigen->decrement('quantity', $amount);

                if ($amount > 0) { // Registrar movimiento solo si la cantidad devuelta es > 0
                    InventoryMovement::create([
                        'product_id' => $stockId,
                        'from_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                        'from_location_id' => $this->branchId, // Sucursal de Origen (Devuelve)
                        'to_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                        'to_location_id' => $branchId, // Sucursal de Destino (Recibe)
                        'old_quantity' => $oldQuantityOrigen,
                        'new_quantity' => $newQuantityOrigen,
                        'difference' => $amount * -1, // La diferencia es negativa para el stock de origen
                        'type' => WarehouseStockHistory::MOVEMENT_TYPE_REFUND,
                        'user_id' => Auth::id(),
                    ]);
                }
                // -----------------------------------------------------------------


                // 2. GESTIÓN DEL STOCK EN SUCURSAL DE DESTINO (QUIEN RECIBE)
                // -----------------------------------------------------------------
                $attrProdDestino = [
                    'product_id' => $stockId,
                    'branch_id' => $branchId,
                ];
                $productStockDestino = ProductStock::firstOrCreate($attrProdDestino, ['quantity' => 0]);

                $oldQuantityDestino = $productStockDestino->quantity;
                $newQuantityDestino = $oldQuantityDestino + $amount;

                // $productStockDestino->lockForUpdate(); // Bloquear también el destino

                $productStockDestino->increment('quantity', $amount);

                if ($amount > 0) { // Registrar movimiento solo si la cantidad devuelta es > 0
                    InventoryMovement::create([
                        'product_id' => $stockId,
                        'from_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                        'from_location_id' => $branchId, // Sucursal de Destino (Recibe)
                        'to_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                        'to_location_id' => $this->branchId, // Sucursal de Origen (Devuelve)
                        'old_quantity' => $oldQuantityDestino,
                        'new_quantity' => $newQuantityDestino,
                        'difference' => $amount, // La diferencia es positiva para el stock de destino
                        'type' => WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY, // O un tipo de movimiento apropiado
                        'user_id' => Auth::id(),
                    ]);
                }
            } // Fin del foreach

            DB::commit(); // Confirma la transacción (COMMIT)
            Notification::make()
                ->title('Éxito')
                ->body('Se actualizo el inventario')
                ->success()
                ->send();
            return true; // Éxito

        } catch (\Exception $e) {
            DB::rollBack(); // Deshace la transacción (ROLLBACK) si ocurre alguna excepción

            Notification::make()
                ->title('Error')
                ->body('Se produjo un error: ' . $e->getMessage())
                ->danger()
                ->send();
            // Registrar el error para seguimiento
            Log::error('Error al guardar devolución entre sucursales: ' . $e->getMessage(), [
                'exception' => $e,
                'celdas' => $celdas,
                'branchId' => $branchId,
                'user_id' => Auth::id(),
            ]);

            // Si necesitas el mensaje de error para mostrar al usuario, puedes retornarlo
            // throw new \Exception($e->getMessage());
            return false; // Fallo
        }
    }

    public function render()
    {
        return view('livewire.manage-branch');
    }
}
