<?php

namespace App\Livewire\Warehouse;

use App\Livewire\Warehouse;
use App\Models\InventoryMovement;
use App\Models\ProductStock;
use App\Models\WarehouseRefund;
use App\Models\WarehouseIncome;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use App\Rules\CheckBranchSendProducts;
use App\Rules\CheckRefundSendProducts;
use App\Rules\CheckSendProducts;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class VoidWharehouseRefundModal extends Component
{
    public bool $isOpen = false;
    public WarehouseRefund $warehouseRefund;
    public int $warehouseRefundId;

    protected function rules()
    {
        return [
            'warehouseRefundId' => ['required', new CheckRefundSendProducts($this->warehouseRefund->id)],
        ];
    }

    public function mount(int $warehouseRefundId){
        $this->warehouseRefundId = $warehouseRefundId;
        $this->warehouseRefund = WarehouseRefund::find($warehouseRefundId);
        if(strcmp($this->warehouseRefund->status, WarehouseRefund::STATUS_VOID) == 0){
            Notification::make()
            ->title('Error')
            ->body('El registro ya se encuentra como ANULADO.')
            ->danger()
            ->send();
        }
    }

    #[On('open-void-warehouse-refund-modal')]
    public function openModal()
    {
        if(strcmp($this->warehouseRefund->status, WarehouseRefund::STATUS_VOID) == 0){
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

    public function closeVoidWherhouseRefundModal()
    {
        $this->isOpen = false;
    }

    public function voidRefundRegister(){
        $this->validate();

        $this->warehouseRefund->refresh();
        if(strcmp($this->warehouseRefund->status, WarehouseRefund::STATUS_VOID) == 0){
            Notification::make()
            ->title('Error')
            ->body('El registro ya se encuentra como ANULADO.')
            ->danger()
            ->send();
            $this->closeVoidWherhouseRefundModal();
            return;
        }

        // if(!$this->warehouseRefund->base_code){
        //     Notification::make()
        //     ->title('Error')
        //     ->body('El registro de entrega tiene productos diferentes a productos OPTICOS.')
        //     ->danger()
        //     ->send();
        //     $this->closeVoidWherhouseRefundModal();
        //     return;
        // }
        
        $warehouseStockHistories = WarehouseStockHistory::where('movement_type', WarehouseStockHistory::MOVEMENT_TYPE_REFUND)
            ->where('type_id', $this->warehouseRefund->id)->get();
        $warehouseMid = $this->warehouseRefund->warehouse_id;


        try{
            DB::transaction(function () use ($warehouseStockHistories, $warehouseMid) {
                $this->warehouseRefund->status = WarehouseRefund::STATUS_VOID;
                $this->warehouseRefund->save();
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
                    $newQuantity = $oldQuantity - $amount;

                    $warehouseStock->increment('quantity', ($amount * (-1)));

                    $branchId = $this->warehouseRefund->branch_id;

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
                            'type' => WarehouseStockHistory::MOVEMENT_TYPE_VOID_REFUND,
                            'type_id' => $this->warehouseRefund->id,
                            'user_id' => Auth::id(),
                        ]);
                    }
                }

                $this->closeVoidWherhouseRefundModal();
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
            $this->closeVoidWherhouseRefundModal();
        }
    }

    public function render()
    {
        return view('livewire.warehouse.void-wharehouse-refund-modal');
    }
}
