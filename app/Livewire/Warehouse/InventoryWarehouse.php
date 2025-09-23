<?php

namespace App\Livewire\Warehouse;

use App\Models\Branch;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use function PHPUnit\Framework\isEmpty;

class InventoryWarehouse extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $warehouseId;
    public $querySearch = '';

    public function mount($warehouseId): void
    {
        $this->warehouseId = $warehouseId;
    }

    public function search()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::query();
//        dd("cccc 1:: " . $this->querySearch);
        if(strcmp($this->querySearch, "") != 0){
            $query->where(function($subQuery){
                $subQuery->where('name', 'like', '%' . $this->querySearch . '%')
                    ->orWhere('code', 'like', '%' . $this->querySearch . '%');
            });
        }

        $products = $query->orderBy("name")->paginate(5);
        return view('livewire.warehouse.inventory-warehouse',  [
            'products' => $products,
        ]);
    }
}
