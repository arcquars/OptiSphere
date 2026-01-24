<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class HistoryProductModal extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public $branch;
    public ?Product $product = null;

    protected $listeners = ['toggleOpenHistoryModal' => 'openHistoryModal'];

    public function mount($branchId){
        $this->branch = Branch::find($branchId);
    }

    public function openHistoryModal($product){
        $productId = explode('-', $product)[1];
        $this->product = Product::find($productId);
        $this->resetPage('movsPage');
        $this->showForm = !$this->showForm;
    }

    public function closeModal(){
        $this->showForm = false;
    }

    public function render()
    {
        $movements = Collection::empty();
        if($this->product){
            $movements = InventoryMovement::where('product_id', $this->product->id)
            ->where(function($query) {
                $query->where([
                    ['from_location_type', 'like', 'SUCURSAL'],
                    ['from_location_id', $this->branch->id]
                ])
                ->orWhere([
                    ['to_location_type', 'like', 'SUCURSAL'],
                    ['to_location_id', $this->branch->id]
                ]);
            })
            ->orderByDesc('created_at')->paginate(config('cerisier.pagination', 3), pageName: 'movsPage');
        }
        

        return view('livewire.branch.history-product-modal', compact('movements'));
    }
}
