<?php

namespace App\Livewire\Sale;

use App\Models\InventoryMovement;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\Product; // 👈 ajusta al namespace real de tu modelo
use App\Models\ProductStock;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
// use App\Models\SaleItem; // 👈 ajusta al namespace real
use Livewire\Component;

class ViewSaleModal extends Component
{
    public bool $showForm = false;
    public Sale $sale;
    public ?Promotion $promotion = null;
    public array $editItems = [];

    // Búsqueda por item: ['itemId' => 'query']
    public array $searchQuery = [];

    // Resultados por item: ['itemId' => [...productos]]
    public array $searchResults = [];

    protected $listeners = ['toggleViewSale' => 'toggleForm'];

    public function toggleForm($saleId = null): void
    {
        if ($saleId) {
            $this->resetValidation();
            $this->searchQuery   = [];
            $this->searchResults = [];
            $this->editItems     = [];

            $this->sale      = Sale::find($saleId);
            $this->promotion = null;

            if ($this->sale && $this->sale->use_promotion) {
                foreach ($this->sale->items as $item) {
                    if ($item->promotion_id) {
                        $this->promotion = Promotion::find($item->promotion_id);
                        break;
                    }
                }
            }
        }

        $this->showForm = ! $this->showForm;
    }

    public function editItem(int $itemId): void
    {
        if (in_array($itemId, $this->editItems)) {
            $this->editItems     = array_diff($this->editItems, [$itemId]);
            $this->searchQuery   = array_diff_key($this->searchQuery,   [$itemId => null]);
            $this->searchResults = array_diff_key($this->searchResults, [$itemId => null]);
        } else {
            $this->editItems[]          = $itemId;
            $this->searchQuery[$itemId] = '';
        }
    }

    public function cancelEdit(int $itemId): void
    {
        $this->editItems     = array_diff($this->editItems, [$itemId]);
        unset($this->searchQuery[$itemId], $this->searchResults[$itemId]);
    }

    // Se llama con wire:model en tiempo real
    public function updatedSearchQuery(string $value, string $itemId): void
    {
        $query = trim($value);

        if (strlen($query) < 2) {
            $this->searchResults[$itemId] = [];
            return;
        }

        $branchId = $this->sale->branch_id;
        $item = SaleItem::where('salable_type', Product::class)->where('id', $itemId)->first();
        $quantity = $item ? $item->quantity : 0;

        // Ajusta Product y los campos a tu modelo real
        $this->searchResults[$itemId] = Product::query()
            ->where('id', '!=', $item->salable()->first()->id) // Excluye el producto actual
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%");
            })
            ->whereHas('stocks', function($q) use ($branchId, $quantity) {
                $q->where('branch_id', $branchId)->where('quantity', '>=', $quantity);
            })
            ->select('id', 'name', 'code') // solo lo necesario
            ->limit(8)
            ->get()
            ->toArray();
    }

    public function replaceItemProduct(int $itemId, int $productId): void
    {
        
        $item = SaleItem::find($itemId);

        // dd($item->salable->id . " || " . $productId);
        // dd($productId . " || " . $item->salable->id);
        if (! $item || $item->sale_id !== $this->sale->id) {
            return; // seguridad: solo items de esta venta
        }

        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        // 1. Obtener y actualizar ProductStock Para el producto Nuevo (salida)
        $productStock = ProductStock::where('product_id', $productId)
            ->where('branch_id', $this->sale->branch_id)
            ->first();

        $oldQuantity = $productStock->quantity;
        $newQuantity = $oldQuantity - $item->quantity; // Asumiendo que quieres deducir la cantidad del item vendido

        $productStock->update(['quantity' => $newQuantity]);

        Log::info("Producto ID {$productId} stock actualizado: {$oldQuantity} -> {$newQuantity}");
        InventoryMovement::create([
            'product_id' => $productId,
            'from_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
            'from_location_id' => $this->sale->branch_id,
            'to_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH, // La venta es el destino final
            'to_location_id' => $this->sale->branch_id,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'difference' => -$item->quantity, // La diferencia es negativa (salida)
            'type' => 'EDITAR VENTA',
            'description' => "Venta registrada, movimiento de salida de la sucursal {$this->sale->branch->name}. Venta ID: {$this->sale->id}",
            'user_id' => Auth::id(),
        ]);

        // 2. Obtener y actualizar ProductStock Para el producto Nuevo (entrada/devolucion)
        $productStock = ProductStock::where('product_id', $item->salable->id)
            ->where('branch_id', $this->sale->branch_id)
            ->first();

        $oldQuantity = $productStock->quantity;
        $newQuantity = $oldQuantity + $item->quantity; // Asumiendo que quieres sumar la cantidad del item vendido

        $productStock->update(['quantity' => $newQuantity]);

        Log::info("Producto ID {$item->salable->id} stock actualizado: {$oldQuantity} -> {$newQuantity}");
        InventoryMovement::create([
            'product_id' => $item->salable->id,
            'from_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
            'from_location_id' => $this->sale->branch_id,
            'to_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH, // La venta es el destino final
            'to_location_id' => $this->sale->branch_id,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'difference' => $item->quantity, // La diferencia es positiva (entrada)
            'type' => 'EDITAR VENTA',
            'description' => "Venta registrada, movimiento de entrada de la sucursal {$this->sale->branch->name}. Venta ID: {$this->sale->id}",
            'user_id' => Auth::id(),
        ]);


        // Ajusta los campos según tu modelo SaleItem
        $item->update([
            'salable_id'   => $product->id,
            'salable_type' => Product::class,
        ]);




        // Actualizar ProductStock y InventoryMovement si es necesario aquí
        // Esto para el producto nuevo y el antiguo, dependiendo de tu lógica de negocio

        // // Limpia el estado de edición del item
        $this->cancelEdit($itemId);

        // // Refresca los items de la venta
        $this->sale->refresh();
    }

    public function render()
    {
        return view('livewire.sale.view-sale-modal');
    }
}