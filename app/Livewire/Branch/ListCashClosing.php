<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListCashClosing extends Component
{
    public $branches;
    public $branchSelect;

    public function setBranchSelect($branchId){
        $this->branchSelect = $branchId;
        /** @var Branch $branch */
        $branch = Branch::find($branchId);
        if(!$branch->isOpenCashBoxClosingByUser(Auth::id())){
            Notification::make()
                ->title('Cerrar Caja')
                ->body("No tiene una Caja Abierta para la sucursal: " . $branch->name)
                ->danger()
                ->send();
            $this->branchSelect = null;
//            return;
        }
        $this->dispatch('load-by-branch', branchId: $this->branchSelect);
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
