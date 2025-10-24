<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListCashClosing extends Component
{
    public $branches;
    public $branchSelect;

    public function setBranchSelect($branchId){
        $this->branchSelect = $branchId;
        $this->dispatch('load-by-branch', branchId: $branchId);
    }

    public function mount(): void
    {
//        $this->branchSelect = 1;
        $this->branches = User::find(Auth::id())->branches;
        if(Auth::user()->hasRole('admin')){
            $this->branches = Branch::where('is_active', true)->get();
        }
    }

    public function render()
    {
        return view('livewire.branch.list-cash-closing');
    }
}
