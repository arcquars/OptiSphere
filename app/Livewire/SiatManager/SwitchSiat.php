<?php

namespace App\Livewire\SiatManager;

use Livewire\Attributes\On;
use Livewire\Component;
use Log;
use function Livewire\on;

class SwitchSiat extends Component
{
    public int $branchId;
    public int $active;

    public function mount($active, $branchId) {
        $this->branchId = $branchId;
        $this->active = $active;
    }
    
    #[On('switch-reload')]
    public function reloadActive($active){
        Log::info('Active::::: ' . $active);
        $this->active = $active;
    }

    public function render()
    {
        return view('livewire.siat-manager.switch-siat');
    }
}
