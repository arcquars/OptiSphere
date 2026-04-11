<?php

namespace App\Livewire\Warehouse;

use App\Livewire\Warehouse;
use App\Models\WarehouseIncome;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use App\Rules\CheckSendProducts;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class VoidWharehouseIncomeModal extends Component
{
    public bool $isOpen = false;
    public $warehouseInvoice;
    public $warehouseInvoiceId;

    protected function rules()
    {
        return [
            'warehouseInvoiceId' => ['required', new CheckSendProducts($this->warehouseInvoice->id, 'INGRESO')],
        ];
    }

    public function mount($warehouseInvoiceId){
        $this->warehouseInvoiceId = $warehouseInvoiceId;
        $this->warehouseInvoice = WarehouseIncome::find($warehouseInvoiceId);
        if(strcmp($this->warehouseInvoice->status, WarehouseIncome::STATUS_VOID) == 0){
            Notification::make()
            ->title('Error')
            ->body('El registro ya se encuentra como ANULADO.')
            ->danger()
            ->send();
        }
    }

    #[On('open-void-warehouse-invoice-modal')]
    public function openModal()
    {
        if(strcmp($this->warehouseInvoice->status, WarehouseIncome::STATUS_VOID) == 0){
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

    public function closeVoidWherhouseIncomeModal()
    {
        $this->isOpen = false;
    }

    public function voidRegister(){
        $this->validate();

        $this->warehouseInvoice->refresh();
        if(strcmp($this->warehouseInvoice->status, WarehouseIncome::STATUS_VOID) == 0){
            Notification::make()
            ->title('Error')
            ->body('El registro ya se encuentra como ANULADO.')
            ->danger()
            ->send();
            $this->closeVoidWherhouseIncomeModal();
            return;
        }

        if(!$this->warehouseInvoice->base_code){
            Notification::make()
            ->title('Error')
            ->body('El registro de ingreso tiene productos diferentes a productos OPTICOS.')
            ->danger()
            ->send();
            $this->closeVoidWherhouseIncomeModal();
            return;
        }
        
        $warehouseStockHistories = WarehouseStockHistory::where('movement_type', "INGRESO")
            ->where('type_id', $this->warehouseInvoice->id)->get();
        $warehouseMid = $this->warehouseInvoice->warehouse_id;

        try{
            DB::transaction(function () use ($warehouseStockHistories, $warehouseMid) {
                $this->warehouseInvoice->status = WarehouseIncome::STATUS_VOID;
                $this->warehouseInvoice->save();
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

                    $warehouseStock->increment('quantity', ($amount*(-1)));
                }

                $this->closeVoidWherhouseIncomeModal();
                $this->dispatch('sphere-updated');
            });
        } catch (Exception $e){
            dd($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.warehouse.void-wharehouse-income-modal');
    }
}
