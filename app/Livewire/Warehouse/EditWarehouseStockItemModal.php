<?php

namespace App\Livewire\Warehouse;

use App\Services\WarehouseStockHistoriService;
use Livewire\Component;
use App\Models\Product;
use App\Models\WarehouseStock;
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
    public $minAmount = 0;
    public $maxAmount = 100;
    public $quantity = 0;
    public $isNew = true;
    public $warehouseTypeId;
    public $action;
    public $warehouseId;

    protected function rules()
    {
        return [
            'amount' => ['required', 'integer', 'min:'.$this->minAmount, 'max:'.$this->maxAmount],
        ];
    }

    public function mount(?int $historyId = null, ?string $action = null, ?int $productId = null, ?int $warehouseId = null)
    {
        if($historyId && $action && $productId && $warehouseId){
            $this->loadProduct($historyId, $action, $productId, $warehouseId);
        }
    }

    #[On('open-edit-warehouse-stock-modal')]
    public function loadProduct($historyId, $action, $productId, $warehouseId)
    {
        $this->warehouseTypeId = $historyId;
        // Normalizamos a los alias cortos usados en toda la app (INGRESO / ENTREGA),
        // sin importar si llega el alias corto (HistoryShow) o el movement_type
        // completo (HistoryMovementShow, ej. "ENTREGA_SUCURSAL").
        $this->action = match (true) {
            str_contains($action, 'ENTREGA') => 'ENTREGA',
            str_contains($action, 'INGRESO') => 'INGRESO',
            default => $action,
        };
        $this->warehouseId = $warehouseId;

        $this->warehouseStockHistory = WarehouseStockHistory::
            where('movement_type', 'like', "%".$this->action."%")
            ->where('type_id', $historyId)
            ->whereHas('warehouseStock', function($query) use ($productId){
                $query->where('product_id', $productId);
            })
            ->first();

        $this->product = Product::find($productId);

        $warehouseStock = WarehouseStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
        $currentWarehouseQty = $warehouseStock->quantity ?? 0;

        if($this->warehouseStockHistory){
            $this->isNew = false;
            $this->amount = $this->warehouseStockHistory->difference;
            $this->quantity = $this->warehouseStockHistory->warehouseStock->quantity;

            if($this->action === 'ENTREGA'){
                // Stock que había en el almacén ANTES de esta entrega. La cantidad
                // actual ya tiene restada la entrega, por eso se la "devolvemos".
                $availableBeforeThisDelivery = $currentWarehouseQty + $this->warehouseStockHistory->difference;
                $this->minAmount = 0;
                $this->maxAmount = $availableBeforeThisDelivery;
            } else {
                $this->minAmount = $this->warehouseStockHistory->warehouseStock->quantity - $this->warehouseStockHistory->difference;
                if($this->minAmount <= 0){
                    $this->minAmount = ($this->minAmount * -1) + 1;
                } else {
                    $this->minAmount = 0;
                }
                $this->maxAmount = 100;
            }
        } else {
            $this->isNew = true;
            $this->amount = 0;
            $this->quantity = $currentWarehouseQty;

            if($this->action === 'ENTREGA'){
                // Producto nuevo dentro de una entrega existente: el tope es
                // todo lo disponible actualmente en el almacén.
                $this->minAmount = 0;
                $this->maxAmount = $currentWarehouseQty;
            } else {
                $this->minAmount = 0;
                $this->maxAmount = 100;
            }
        }
        $this->isOpen = true;

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
        $this->validate();

        $warehouseStockHistoriService = new WarehouseStockHistoriService();

        try {
            if($this->action === 'ENTREGA'){
                if($this->isNew){
                    $warehouseStockHistoriService->createSingleDelivery($this->warehouseTypeId, $this->product->id, $this->amount);
                } else {
                    $warehouseStockHistoriService->updateSingleDelivery($this->warehouseStockHistory->id, $this->amount);
                }
            } else {
                if($this->isNew){
                    $warehouseStockHistoriService->createSingleIncome($this->warehouseTypeId, $this->product->id, $this->amount);
                } else {
                    $warehouseStockHistoriService->updateSingleIncome($this->warehouseStockHistory->id, $this->amount);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error al editar movimiento de stock: ' . $e->getMessage());
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title('Éxito')
            ->body('El registro se ha guardado correctamente.')
            ->success()
            ->send();
        $this->closeModal();
        $this->dispatch('sphere-updated');
        $this->dispatch('history_movement-show-updated');
        $this->dispatch('close-modal', id: 'table-action-modal');
    }
}