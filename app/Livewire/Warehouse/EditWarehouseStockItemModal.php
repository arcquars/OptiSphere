<?php

namespace App\Livewire\Warehouse;

use App\Services\WarehouseStockHistoriService;
use Livewire\Component;
use App\Models\Product;
use App\Models\WarehouseStockHistory;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class EditWarehouseStockItemModal extends Component
{
    public bool $isOpen = false;
    public ?Product $product = null;
    
    public ?WarehouseStockHistory $warehouseStockHistory = null;

    public $amount;
    public $minAmount = 1;

    protected function rules()
    {
        return [
            'amount' => 'required|integer|min:'.$this->minAmount.'|max:100',
        ];
    }

    #[On('open-edit-warehouse-stock-modal')]
    public function loadProduct($historyId, $action, $productId)
    {
        $this->warehouseStockHistory = WarehouseStockHistory::
            where('movement_type', 'like', "%".$action."%")
            ->where('type_id', $historyId)
            ->whereHas('warehouseStock', function($query) use ($productId){
                $query->where('product_id', $productId);
            })
            ->first();

        $this->product = Product::find($productId);
        $this->amount = $this->warehouseStockHistory->difference;
    
        $this->minAmount = $this->warehouseStockHistory->warehouseStock->quantity - $this->warehouseStockHistory->difference;
        if($this->minAmount <= 0){
            $this->minAmount = ($this->minAmount * -1) + 1;
        } else {
            $this->minAmount = 1;
        }
        Log::info("PDM EditWarehouseStockItemModal 1:: " . $productId);
        // Log::info("PDM EditWarehouseStockItemModal 2:: " . json_encode($this->prices));
        if ($this->product) {
            $this->isOpen = true;
        }
    }

    public function closeModal()
    {
        $this->isOpen = false;
        // Opcional: limpiar el objeto para liberar memoria
        $this->product = null;
        $this->clearValidation();
    }

    public function render()
    {
        return view('livewire.warehouse.edit-warehouse-stock-item-modal');
    }

    public function updateRegister(){
        $result = $this->validate();
        // dd("xxx");
        $warehouseStockHistoriService = new WarehouseStockHistoriService();
        $warehouseStockHistoriService->updateSingleIncome($this->warehouseStockHistory->id, $this->amount);

        Notification::make()
            ->title('Éxito')
            ->body('El registro se ha guardado correctamente.')
            ->success()
            ->send();
        $this->closeModal();
    }
}
