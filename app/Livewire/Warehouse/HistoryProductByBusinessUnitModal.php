<?php

namespace App\Livewire\Warehouse;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;

class HistoryProductByBusinessUnitModal extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public $branch;
    public ?Product $product = null;

    public $bu = null;
    public $buType = null;

    protected $listeners = ['toggleOpenHistoryBuModal' => 'openHistoryBuModal'];

    public function mount($businessUnitType, $businessUnitId){
        $this->branch = Branch::find($businessUnitId);
        $this->buType = $businessUnitType;
        $this->bu = ($businessUnitType === Warehouse::BUSINESS_BRANCH) 
            ? Branch::find($businessUnitId) 
            : Warehouse::find($businessUnitId);
    }

    public function openHistoryBuModal($product){
        if (filter_var($product, FILTER_VALIDATE_INT) !== false) {
            $this->product = Product::find($product);
        } else {
            $productId = (count(explode('-', $product)) >= 1) ? explode('-', $product)[1] : null;
            $this->product = Product::find($productId);
        }
        
        $this->resetPage('movsPage');
        $this->showForm = !$this->showForm;
    }

    public function closeModal(){
        $this->showForm = false;
    }

    public function render()
    {
        $buType = $this->buType;
        $movements = Collection::empty();
        if($this->product){
            $movements = InventoryMovement::where('product_id', $this->product->id)
            ->where(function($query) use ($buType) {
                $query->where([
                    ['from_location_type', 'like', $buType],
                    ['from_location_id', $this->bu->id]
                ])
                ->orWhere([
                    ['to_location_type', 'like', $buType],
                    ['to_location_id', $this->bu->id]
                ]);
            })
            ->orderByDesc('created_at')->paginate(config('cerisier.pagination', 3), pageName: 'movsPage');
        }

        return view('livewire.warehouse.history-product-by-business-unit-modal', compact('movements'));
    }
}
