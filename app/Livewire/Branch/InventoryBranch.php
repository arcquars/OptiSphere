<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class InventoryBranch extends Component
{
    use WithPagination, WithoutUrlPagination;

    public Branch $branch;
    public $querySearch = '';

    public function mount($branchId): void
    {
        $this->branch = Branch::find($branchId);
    }

    public function search()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::query();
        if(strcmp($this->querySearch, "") != 0){
            $query->where(function($subQuery){
                $subQuery->where('name', 'like', '%' . $this->querySearch . '%')
                    ->orWhere('code', 'like', '%' . $this->querySearch . '%');
            });
        }

        $query->with('stocks', function ($q){
            $q->where('branch_id', $this->branch->id);
        });

        $products = $query->orderBy("name")->paginate(5);
        return view('livewire.branch.inventory-branch', [
            'products' => $products,
        ]);
    }
}
