<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\OpticalProperty;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseIncome;
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
use Livewire\Component;

class Warehouse extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    public $key;

    public $warehouseId;
    public $branchId;
    public $baseCode;
    public $action = "";
    public $type;

    public $matrix = [];
    public $uniqueSpheres = [];
    public $uniqueCylinders = [];

    public ?array $data = [];

    public $branches;

    public function mount($warehouseId): void
    {
        $this->warehouseId = $warehouseId;
        $this->branches = Branch::where('is_active', "=",true)->get();
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

                    $amount = null;
                    $description = "";
                    if(strcmp($this->action, "precios") == 0){
                        $textPrices = $op->product->textPrices();
                        $description =implode(",", $textPrices);
                        $amount = (count($textPrices) > 0)? count($textPrices) : null;
                    } else if(strcmp($this->action, "saldo") == 0 || strcmp($this->action, "entregas") == 0) {
                        $amount = $op->product->stockByWarehouse($this->warehouseId);
                        $description = $amount;
                    }
                    $row[] = [
                        'id' => $op->product_id,
                        'type' => $op->type,
                        'sphere' => $op->sphere,
                        'cylinder' => $op->cylinder,
                        'description' => $description,
                        'amount' => $amount];

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
                                'ingreso' => 'Ingreso',
                                'precios' => 'Precios',
                                'entregas' => 'Entregas a Sucursal',
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
                            ->default(true)
                            ->inline()
                    ])
            ])->statePath('data');
    }

    public function create(): void
    {
        $this->baseCode = $this->form->getState()['baseCode'];
        $this->action = $this->form->getState()['action'];
        $this->type = $this->form->getState()['type'];
        $this->loadCylinders();
        $this->dispatch('clear-markedcells');
    }

    public function save($celdas, $branchId = null, $priceType= null){
        switch ($this->action){
            case "ingreso":
                $this->saveIncome($celdas);
                break;
            case "entregas":
                $this->saveDelivery($celdas, $branchId);
                break;
            case "precios":
                $this->savePrices($celdas, $branchId, $priceType);
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

    public function saveIncome($celdas){
        DB::transaction(function () use ($celdas) {
            $warehouseIncome = WarehouseIncome::create([
                'warehouse_id' => $this->warehouseId,
                'user_id' => Auth::id(),
                'income_date' => Carbon::now()
            ]);

            foreach ($celdas as $data) {
                // 'id' es el ID de la tabla 'product_stocks'.
                $stockId = $data['id'];
                // 'amount' es la nueva cantidad que viene del input.
                $amount = (int) $data['amount'];

                $attributes = [
                    'product_id' => $stockId,
                    'warehouse_id' => $this->warehouseId, // Ejemplo: asume que es el almacén 1
                ];
                // Buscar el registro de stock por su ID.
                $warehouseStock = WarehouseStock::firstOrCreate($attributes, [
                    'quantity' => 0 // Inicializa la cantidad en 0 si es un nuevo registro
                ]);

                $oldQuantity = $warehouseStock->quantity;
                $newQuantity = $oldQuantity + $amount;

                $warehouseStock->increment('quantity', $amount);

                if ($amount != 0) {
                    WarehouseStockHistory::create([
                        'warehouse_stock_id' => $warehouseStock->id,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'difference' => $amount,
                        'movement_type' => WarehouseStockHistory::MOVEMENT_TYPE_INCOME,
                        'type_id' => $warehouseIncome->id
                    ]);
                }
            }
        });
    }

    public function saveDelivery($celdas, $branchId){
        DB::transaction(function () use ($celdas, $branchId) {
            $warehouseDelivery = WarehouseDelivery::create([
                'warehouse_id' => $this->warehouseId,
                'branch_id' => $branchId,
                'user_id' => Auth::id(),
                'delivery_date' => Carbon::now()
            ]);

            foreach ($celdas as $data) {
                // 'id' es el ID de la tabla 'product_stocks'.
                $stockId = $data['id'];
                // 'amount' es la nueva cantidad que viene del input.
                $amount = (int) $data['amount'];

                $attributes = [
                    'product_id' => $stockId,
                    'warehouse_id' => $this->warehouseId, // Ejemplo: asume que es el almacén 1
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
                        'from_location_id' => $this->warehouseId,
                        'to_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                        'to_location_id' => $branchId,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'difference' => $amount,
                        'type' => WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY,
                        'user_id' => Auth::id(),
                    ]);
                }
            }
        });
    }

    public function savePrices($celdas, $branchId, $priceType){
        DB::transaction(function () use ($celdas, $priceType, $branchId) {
            foreach ($celdas as $data) {
                $productId = $data['id'];
                $amount = $data['amount'];
                $product = Product::find($productId);

                if (!$product) {
                    continue;
                }
                $searchCondition = [
                    'type' => $priceType,
                    'branch_id' => (strcmp($branchId, '') == 0)? null : $branchId,
                ];

                $updateOrCreateValues = [
                    'price' => $amount,
                ];


                $price = $product->prices()->updateOrCreate(
                    $searchCondition,
                    $updateOrCreateValues
                );

                if ($price->wasRecentlyCreated) {
                    Log::info("PRECIO CREADO: Se creó un nuevo precio normal para el producto ID {$product->id} con valor {$price->price}.");
                } else {
                    Log::info( "PRECIO ACTUALIZADO: El precio normal existente para el producto ID {$product->id} fue actualizado a {$price->price}.\n");
                }

            }
        });
    }

    public function render()
    {
        return view('livewire.warehouse');
    }
}
