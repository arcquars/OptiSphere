<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Http\Requests\SendProductsRequest;
use App\Models\InventoryMovement;
use App\Models\OpticalProperty;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseIncome;
use App\Models\WarehouseRefund;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use App\Rules\CheckSendProducts;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

use function Livewire\on;

class HistoryShow extends Page
{
    public $baseCode;
    public $userM;
    public $action = "";
    public $type;
    public $warehouse_name;
    // Warehouse tipo movimiento (income, delivery, refund)
    public $warehouse_m;
    public $warehouse_m_id;
    public $warehouse_id;

    public $dateMovement;

    public $bgAction;
    public $matrix = [];
    public $uniqueSpheres = [];
    public $uniqueCylinders = [];

    // Propiedades para el modal
    public $selectedBranchId;


    public $warehouseStockHistories;
    protected static ?string $title = 'Ver Historial de Movimiento';
    protected static string $resource = WarehouseResource::class;

    // 1. Activamos carga perezosa
    public static bool $isLazy = true;

    // 2. Definimos qué se ve mientras carga (opcional)
    public function getPlaceholderHtml(): ?string
    {
        return view('filament.components.loading-skeleton')->render();
    }
    
    protected function rules()
    {
        return [
            'selectedBranchId' => ['required', 'exists:branches,id', new CheckSendProducts($this->warehouse_m_id, $this->action)],
        ];
    }

    public function mount($history_id, $action, $type, $code): void
    {
        $warehouseM = null;
        switch($action){
            case "INGRESO":
                $warehouseM = WarehouseIncome::find($history_id);
                $this->bgAction = "success";
                break;
            case "ENTREGA":
                $warehouseM = WarehouseDelivery::find($history_id);
                $this->bgAction = "info";
                break;
            default:
                $warehouseM = WarehouseRefund::find($history_id);
                $this->bgAction = "warning";
                break;

        }

        $this->userM = User::find($warehouseM->user_id);
        $this->warehouse_name = $warehouseM->warehouse->name;
        $this->warehouse_m = $warehouseM;
        $this->warehouse_m_id = $warehouseM->id;
        $this->warehouse_id = $warehouseM->warehouse_id;
        $this->dateMovement = $warehouseM->created_at;
        $this->baseCode = $code;
        $this->type = $type;
        $this->action = $action;
        // $this->warehouseStockHistories = WarehouseStockHistory::where('movement_type', 'like', "%".$action."%")
        //             ->where('type_id', $history_id)->get();
        $this->refreshHistories();
        $this->loadCylinders();
    }

    public function refreshHistories()
    {
        $this->warehouseStockHistories = WarehouseStockHistory::where('movement_type', 'like', "%".$this->action."%")
            ->where('type_id', $this->warehouse_m_id) // Asumiendo que usas las propiedades de tu clase
            ->get();
    }

    #[On('sphere-updated')]
    public function loadCylinders(){
        if($this->baseCode){
            $this->refreshHistories();
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

    public function sendToBranch()
    {
        $this->validate();

        $branchId = $this->selectedBranchId;
        $warehouseStockHistories = WarehouseStockHistory::where('movement_type', $this->action)
            ->where('type_id', $this->warehouse_m_id)->get();
        $warehouseMid = $this->warehouse_id;
        $baseCodeAux = $this->baseCode;

        DB::transaction(function () use ($warehouseStockHistories, $branchId, $warehouseMid, $baseCodeAux) {
            $warehouseDelivery = WarehouseDelivery::create([
                'warehouse_id' => $warehouseMid,
                'branch_id' => $branchId,
                'user_id' => Auth::id(),
                'base_code' => $baseCodeAux,
                'delivery_date' => Carbon::now()
            ]);

            foreach ($warehouseStockHistories as $data) {
                // 'id' es el ID de la tabla 'product_stocks'.
                $stockId = $data->warehouseStock->product_id;
                $op = OpticalProperty::where('product_id', $stockId)->first();

                if(!$op){
                    continue; // Saltar este registro si el base_code no coincide
                }
                if($op && $op->base_code != $baseCodeAux){
                    continue; // Saltar este registro si el base_code no coincide
                }

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

    protected string $view = 'filament.resources.warehouses.pages.history-show';
}
