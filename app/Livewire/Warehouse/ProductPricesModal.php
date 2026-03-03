<?php

namespace App\Livewire\Warehouse;

use App\Models\Branch;
use App\Models\OpticalProperty;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductPricesModal extends Component
{
    public bool $isOpen = false;
    public ?Product $product = null;
    public $branches;
    public $prices;
    
    #[On('open-product-prices-modal')]
    public function loadProduct($productId)
    {
        $this->branches = Branch::where('is_active', true)->get();
        $this->product = Product::find($productId);
        $this->prices = Price::where('priceable_type', Product::class)
            ->where('priceable_id', $this->product->id)->get();
    
        Log::info("dddd 1:: " . $productId);
        Log::info("dddd 2:: " . json_encode($this->prices));
        if ($this->product) {
            $this->isOpen = true;
        }
    }

    public function closeModal()
    {
        $this->isOpen = false;
        // Opcional: limpiar el objeto para liberar memoria
        $this->product = null;
    }

    public function render()
    {
        return view('livewire.warehouse.product-prices-modal');
    }
}
