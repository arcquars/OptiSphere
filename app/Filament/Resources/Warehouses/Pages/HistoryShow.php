<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Models\OpticalProperty;
use App\Models\User;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseIncome;
use App\Models\WarehouseRefund;
use App\Models\WarehouseStockHistory;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;

class HistoryShow extends Page
{
    public $baseCode;
    public $userM;
    public $action = "";
    public $type;
    public $warehouse_name;
    public $dateMovement;

    public $bgAction;
    public $matrix = [];
    public $uniqueSpheres = [];
    public $uniqueCylinders = [];

    public $warehouseStockHistories;
    protected static ?string $title = 'Ver Historial de Movimiento';
    protected static string $resource = WarehouseResource::class;

    public function mount($history_id, $action, $type): void
    {
        $warehouseM = null;
        $this->warehouseStockHistories = WarehouseStockHistory::where('movement_type', 'like', "%".$action."%")
                    ->where('type_id', $history_id)->get();

        switch($action){
            case "INGRESO":
                $warehouseM = WarehouseIncome::find($history_id);
                $this->bgAction = "bg-success";
                break;
            case "ENTREGA":
                $warehouseM = WarehouseDelivery::find($history_id);
                $this->bgAction = "bg-info";
                break;
            default:
                $warehouseM = WarehouseRefund::find($history_id);
                $this->bgAction = "bg-warning";
                break;

        }

        $this->userM = User::find($warehouseM->user_id);
        $this->warehouse_name = $warehouseM->warehouse->name;
        $this->dateMovement = $warehouseM->created_at;
        $this->baseCode = $warehouseM->base_code;
        $this->action = $action;
        $this->type = $type;
        $this->loadCylinders();
    }

    public function loadCylinders(){
        if($this->baseCode){
            $this->matrix = [];
            $opticalProperties = OpticalProperty::where('base_code', $this->baseCode)
                ->where('type', $this->type)
                ->whereHas('product', function ($query){
                    $query->where('is_active', true);
                })
                ->get();

            $this->uniqueSpheres = $opticalProperties->pluck('sphere')->unique()->sort()->values();
            $this->uniqueCylinders = $opticalProperties->pluck('cylinder')->unique()->sort()->values();

            foreach ($this->uniqueSpheres as $sphere){
                $row = [];
                foreach ($this->uniqueCylinders as $cylinder){
                    /** @var OpticalProperty $op */
                    $op = OpticalProperty::where('base_code', $this->baseCode)
                        ->where('type', $this->type)
                        ->where('sphere', $sphere)
                        ->where('cylinder', $cylinder)
                        ->first();

                    $amount = null;
                    $description = "";
                    foreach($this->warehouseStockHistories as $whsh){
                        if($whsh->warehouseStock->product->id == $op->product_id){                            
                            //$amount = $whsh->warehouseStock->quantity . "xx";
                            $amount = $whsh->difference;
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
