<?php

namespace App\Livewire;

use App\Models\OpticalProperty;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Warehouse extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    public $key;

    public $warehouseId;
    public $baseCode;
    public $action;
    public $type;

    public $matrix = [];
    public $uniqueSpheres = [];
    public $uniqueCylinders = [];

    public ?array $data = [];

    public function mount($warehouseId): void
    {
        $this->warehouseId = $warehouseId;
        $this->form->fill();
    }

    public function loadCylinders(){
        if($this->baseCode){
            $this->matrix = [];
            $opticalProperties = OpticalProperty::where('base_code', $this->baseCode)
                ->where('type', $this->type? "+" : "-")
                ->whereHas('product', function ($query){
                    $query->where('is_active', $this->warehouseId);
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
                    if(!strcmp($this->action, "saldo")) {
                        $row[] = [
                            'id' => $op->id,
                            'type' => $op->type,
                            'sphere' => $op->sphere,
                            'cylinder' => $op->cylinder,
                            'amount' => $op->stockByWarehouse($this->warehouseId)];
                    }else
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
                                'ingreso' => 'Ingreso',
                                'precios' => 'Precios',
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

    public function saveIncome($celdas){
        DB::transaction(function () use ($celdas) {
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
                $newQuantity = $oldQuantity + $amount;

                $warehouseStock->increment('quantity', $amount);

                if ($amount != 0) {
                    WarehouseStockHistory::create([
                        'warehouse_stock_id' => $warehouseStock->id,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'difference' => $amount,
                    ]);
                }
            }
        });
        Notification::make()
            ->title('Éxito')
            ->body('El registro se ha guardado correctamente.')
            ->success()
            ->send();
        $this->dispatch('clear-markedcells');
//        dd($celdas);
    }

    public function render()
    {
        return view('livewire.warehouse');
    }
}
