<?php

namespace App\Livewire\SiatManager;

use Livewire\Component;

class MenuSiat extends Component
{
    public int $siatActive = 1;

    public function mount() {
        
    }

    public function render()
    {
        return view('livewire.siat-manager.menu-siat');
    }
}
