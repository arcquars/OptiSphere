<?php

namespace App\Livewire\Warehouse;

use App\Livewire\Warehouse;
use App\Models\InventoryMovement;
use App\Models\ProductStock;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseIncome;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use App\Rules\CheckBranchSendProducts;
use App\Rules\CheckSendProducts;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class VoidWharehouseDeliveryModal extends Component
{
    public bool $isOpen = false;
    public $warehouseDelivery;
    public $warehouseDeliveryId;

    protected function rules()
    {
        return [
            'warehouseDeliveryId' => ['required', new CheckBranchSendProducts($this->warehouseDelivery->id)],
        ];
    }

    public function mount(int $warehouseDeliveryId){
        $this->warehouseDeliveryId = $warehouseDeliveryId;
        $this->warehouseDelivery = WarehouseDelivery::find($warehouseDeliveryId);
        if(strcmp($this->warehouseDelivery->status, WarehouseDelivery::STATUS_VOID) == 0){
            Notification::make()
            ->title('Error')
            ->body('El registro ya se encuentra como ANULADO.')
            ->danger()
            ->send();
        }
    }

    #[On('open-void-warehouse-delivery-modal')]
    public function openModal()
    {
        if(strcmp($this->warehouseDelivery->status, WarehouseDelivery::STATUS_VOID) == 0){
            Notification::make()
            ->title('Error')
            ->body('El registro ya se encuentra como ANULADO.')
            ->danger()
            ->send();
            $this->isOpen = false;
        } else {
            $this->isOpen = true;
        }
    }

    public function closeVoidWherhouseDeliveryModal()
    {
        $this->isOpen = false;
    }

    public function voidDeliveryRegister(){
        $this->validate();

        $this->warehouseDelivery->refresh();
        if(strcmp($this->warehouseDelivery->status, WarehouseDelivery::STATUS_VOID) == 0){
            Notification::make()
            ->title('Error')
            ->body('El registro ya se encuentra como ANULADO.')
            ->danger()
            ->send();
            $this->closeVoidWherhouseDeliveryModal();
            return;
        }

        if(!$this->warehouseDelivery->base_code){
            Notification::make()
            ->title('Error')
            ->body('El registro de entrega tiene productos diferentes a productos OPTICOS.')
            ->danger()
            ->send();
            $this->closeVoidWherhouseDeliveryModal();
            return;
        }
        
        $warehouseStockHistories = WarehouseStockHistory::where('movement_type', WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY)
            ->where('type_id', $this->warehouseDelivery->id)->get();
        $warehouseMid = $this->warehouseDelivery->warehouse_id;

        try{
            DB::transaction(function () use ($warehouseStockHistories, $warehouseMid) {
                $this->warehouseDelivery->status = WarehouseDelivery::STATUS_VOID;
                $this->warehouseDelivery->save();
                foreach ($warehouseStockHistories as $data) {
                    $stockId = $data->warehouseStock->product_id;
                    // 'amount' es la nueva cantidad que viene del input.
                    $amount = (int) $data->difference;

                    $attributes = [
                        'product_id' => $stockId,
                        'warehouse_id' => $warehouseMid, // Ejemplo: asume que es el almacén 1
                    ];
                    // Buscar el registro de stock por su ID.
                    $warehouseStock = WarehouseStock::firstOrCreate($attributes, [
                        'quantity' => 0 // Inicializa la cantidad en 0 si es un nuevo registro
                    ]);;

                    $oldQuantity = $warehouseStock->quantity;
                    $newQuantity = $oldQuantity + $amount;

                    $warehouseStock->increment('quantity', ($amount));

                    $branchId = $this->warehouseDelivery->branch_id;

                    // Buscar registro de stock en sucursal
                    $attrProd = [
                        'product_id' => $stockId,
                        'branch_id' => $branchId, // Ejemplo: asume que es el almacén 1
                    ];
                    $productStock = ProductStock::firstOrCreate($attrProd, [
                        'quantity' => 0 // Inicializa la cantidad en 0 si es un nuevo registro
                    ]);;

                    $oldQuantity = $productStock->quantity;
                    $newQuantity = $oldQuantity - $amount;

                    $productStock->increment('quantity', $amount * (-1));

                    if ($amount != 0) {
                        InventoryMovement::create([
                            'product_id' => $stockId,
                            'from_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                            'from_location_id' => $branchId,
                            'to_location_type' => InventoryMovement::LOCATION_TYPE_warehouse,
                            'to_location_id' => $warehouseMid,
                            'old_quantity' => $oldQuantity,
                            'new_quantity' => $newQuantity,
                            'difference' => $amount,
                            'type' => WarehouseStockHistory::MOVEMENT_TYPE_VOID_DELIVERY,
                            'type_id' => $this->warehouseDelivery->id,
                            'user_id' => Auth::id(),
                        ]);
                    }
                }

                $this->closeVoidWherhouseDeliveryModal();
                $this->dispatch('sphere-updated');
            });
        } catch (Exception $e){
            Log::error('Error al anular la entrega: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
                $this->closeVoidWherhouseDeliveryModal();
        }
    }

    public function render()
    {
        return view('livewire.warehouse.void-wharehouse-delivery-modal');
    }
}
