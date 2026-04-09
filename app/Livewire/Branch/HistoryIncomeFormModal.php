<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\OpticalProperty;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Livewire\Attributes\On;
use Livewire\Component;

class HistoryIncomeFormModal extends Component implements HasForms
{
    use InteractsWithForms;
    use InteractsWithActions; // <- ESTO SOLUCIONA EL ERROR DE [mountAction]
    
    public $branch;

    public bool $showHistoryIncomeOpen = false;

    // Propiedades enlazadas al formulario
    public ?array $formData = [];

    public function mount($branchId): void
    {
        $this->branch = Branch::find($branchId);

        // Inicializa el form con valores por defecto
        $this->form->fill();
    }

    #[On('toggle-history-income')]
    public function toggleHisIncomeOpenForm(): void
    {
        $this->showHistoryIncomeOpen = !$this->showHistoryIncomeOpen;
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        // Select con búsqueda — "Codigo base"
                        // Select::make('base_code')
                        //     ->label('Codigo base')
                        //     ->required()
                        //     ->searchable()
                        //     ->options(fn () => \App\Models\BaseCode::pluck('name', 'id')) // ajusta al modelo real
                        //     ->placeholder('Seleccione una opción')
                        //     ->columnSpan(2),
                        Select::make('base_code')
                            ->label('Codigo base')
                            ->options(OpticalProperty::groupBy('base_code')->pluck('base_code', 'base_code'))
                            ->searchable()
                        ->required()
                        ->columnSpan(2),
                        // Botones Positivo/Negativo — Toggle Buttons
                        \Filament\Forms\Components\ToggleButtons::make('sign')
                            ->label('Positivo/Negativo?')
                            ->options([
                                'positive' => '+',
                                'negative' => '-',
                            ])
                            ->colors([
                                'positive' => 'warning',
                                'negative' => 'danger',
                            ])
                            ->default('positive')
                            ->inline()
                            ->columnSpan(1),

                        // Botón Cargar
                        Actions::make([
                            // Action::make('load')
                            //     ->label('Cargar')
                            //     ->color('primary')
                            //     ->action(fn () => $this->load()),
                        ])->columnSpan(1)->verticallyAlignEnd(),
                    ]),
            ])
            ->statePath('formData');
    }

    public function load()
    {
        $data = $this->form->getState();

        // Lógica de carga con $data['base_code'], $data['action'], $data['sign']
        // dd($data);
        return redirect()->route('filament.branch-manager.pages.branch-history-income', [
            'branchId' => $this->branch->id,
            'codeBase' => $data['base_code'],
            'type' => $data['sign'],
        ]);
    }

    public function render()
    {
        return view('livewire.branch.history-income-form-modal');
    }
}