<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use Livewire\Attributes\On;
use Livewire\Component;

class SearchCustomer extends Component
{
    public $branchId;
    // Estado para la gestión de clientes
    public $customerSearch = '';
    public $searchResults = [];
    public $selectedCustomer = null;

    // Propiedades para el modal de nuevo cliente
    public $newCustomerName = '';
    public $newCustomerNit = '';
    public $newCustomerEmail = '';

    public function mount($branchId){
        $this->branchId = $branchId;
    }

    public function updatedCustomerSearch($value)
    {
        if (strlen($value) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Customer::where(function ($query) use ($value) {
            // Todas las condiciones dentro de esta función se agruparán entre paréntesis en el SQL final
            $query->where('branch_id', '=', $this->branchId)
                ->where('name', 'like', '%' . $value . '%')
                ->orWhere('razon_social', 'like', '%' . $value . '%')
                ->orWhere('nit', 'like', '%' . $value . '%');
        })
            ->take(5)
            ->get();
    }

    #[On('customer-clear-search')]
    public function clearSearch(){
        $this->searchResults = [];
        $this->customerSearch = "";
    }

    public function render()
    {
        return view('livewire.customer.search-customer');
    }
}
