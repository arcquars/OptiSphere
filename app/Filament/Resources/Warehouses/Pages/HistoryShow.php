<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Models\OpticalProperty;
use App\Models\WarehouseIncome;
use App\Models\WarehouseRefund;
use App\Models\WarehouseStockHistory;
use Filament\Resources\Pages\Page;

class HistoryShow extends Page
{
    public $baseCode;
    public $action = "";
    public $type;
    public $matrix = [];
    public $uniqueSpheres = [];
    public $uniqueCylinders = [];

    public $warehouseStockHistories;
    protected static ?string $title = 'Ver Historial de Movimiento';
    protected static string $resource = WarehouseResource::class;

    public function mount($history_id, $action, $type): void
    {
        $warehouseM = null;
        $this->warehouseStockHistories = WarehouseStockHistory::where('movement_type', $action)
                    ->where('type_id', $history_id)->get();

        switch($action){
            case "INGRESO":
                $warehouseM = WarehouseIncome::find($history_id);
                break;
            default:
                $warehouseM = WarehouseRefund::find($history_id);
                break;

        }

        $this->baseCode = $warehouseM->base_code;
        $this->action = $action;
        $this->type = strcmp($type, "1") == 0? "+" : "-";
        $this->loadCylinders();
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
                    foreach($this->warehouseStockHistories as $whsh){
                        if($whsh->warehouseStock->product->id == $op->product_id){
                            
                            $amount = $whsh->warehouseStock->quantity;
                        }
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

    protected string $view = 'filament.resources.warehouses.pages.history-show';
}
