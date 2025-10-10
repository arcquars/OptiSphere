<?php

namespace App\Livewire\Sale;

use App\Models\Promotion;
use App\Models\Sale;
use Livewire\Component;

class ViewSaleModal extends Component
{
    public bool $showForm = false;
    public Sale $sale;
    public ?Promotion $promotion = null;

    protected $listeners = ['toggleViewSale' => 'toggleForm'];

    public function toggleForm($saleId= null): void
    {
        if($saleId){
            // Resetea las propiedades públicas a su estado inicial
//        $this->reset(['priceNormal', 'priceEspecial', 'priceMayor']);

            // Limpia todos los errores de validación
            $this->resetValidation();

            $this->sale = Sale::find($saleId);
            $this->promotion = null;
            if($this->sale && $this->sale->use_promotion){
                foreach ($this->sale->items as $item){
                    if($item->promotion_id){
                        $this->promotion = Promotion::find($item->promotion_id);
                        continue;
                    }
                }
            }
        }
        $this->showForm = !$this->showForm;
    }
    public function render()
    {
        return view('livewire.sale.view-sale-modal');
    }
}
