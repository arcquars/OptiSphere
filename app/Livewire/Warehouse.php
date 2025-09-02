<?php

namespace App\Livewire;

use App\Models\OpticalProperty;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Warehouse extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public $baseCode;
    public $action;

    public $cylinders = [];

    public ?array $data = [];

    public function loadCylinders(){
        if($this->baseCode){
            $this->cylinders = OpticalProperty::where('base_code', $this->baseCode)->groupBy('cylinder')->orderBy('cylinder')->select('cylinder')->get();
            Log::info($this->cylinders);
        }
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns([
                        'sm' => 2,
                        'xl' => 2,
                        '2xl' => 2,
                    ])
                    ->schema([
                        Select::make('baseCode')
                            ->label('Codigo base')
                            ->options(OpticalProperty::groupBy('base_code')->pluck('base_code', 'base_code'))
                            ->searchable()
                        ->required(),
                        Select::make('action')
                            ->options([
                                'saldo' => 'Saldo',
                                'ingreso' => 'Ingreso',
                                'price' => 'Precios',
                            ])
                        ->required(),
                    ])
            ])->statePath('data');
    }

    public function create(): void
    {
        $this->baseCode = $this->form->getState()['baseCode'];
        $this->action = $this->form->getState()['action'];
        $this->loadCylinders();
//        dd($this->form->getState()['baseCode']);
//        dd($this->form->getState());
    }

    public function render()
    {
        return view('livewire.warehouse');
    }
}
