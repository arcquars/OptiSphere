<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateCustomer extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public bool $showForm = false;

    public ?array $data = [];

    #[On('customer-create-open')]
    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        if($this->showForm){
            $this->form->fill();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Razon social')
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state))
                    ->required()->minLength(5)->maxLength(250),
                Select::make('document_type')
                    ->label('Tipo de documento')->options(
                        config('cerisier.tipo_documento_identidad')
                    )->required(),
                Grid::make(4)
                    ->schema([
                        TextInput::make('nit')
                            ->label('NIT/CI')
                            ->columnSpan(3)
                            ->required()
                            ->unique(table: Customer::class)
                            ->minLength(4)->maxLength(18),
                        TextInput::make('complement')
                            ->label('Complemento')
                            ->maxLength(8),
                    ]),
                Grid::make(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('Correo electronico')
                            ->email(),
                        TextInput::make('phone')
                            ->label('Telefono')
                            ->minLength(2)->maxLength(25),
                    ]),
                TextInput::make('address')
                    ->label('Direccion')
                    ->minLength(2)->maxLength(250),
                TextInput::make('contact_info')
                    ->label('Informacion de contacto')
                    ->minLength(2)->maxLength(250),
                Grid::make(2)
                    ->schema([
                        Select::make('type')
                            ->label('Tipo de cliente')
                            ->default('normal')
                            ->disabled(! auth()->user()->hasRole('admin'))
                            ->options(
                                config('cerisier.tipo_cliente')
                            )->required(),
                        Toggle::make('can_buy_on_credit')
                            ->label('Credito')
                            ->default(false)
                            ->disabled(! auth()->user()->hasRole('admin'))
                    ]),
                // ...
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        /** @var Customer $custumerNew */
        $custumerNew = Customer::create($this->form->getState());
        if($custumerNew){
            Notification::make()
                ->title('Éxito')
                ->body('Se creo el cliente correctamente. ' . $custumerNew->id)
                ->success()
                ->send();

            $this->dispatch('customer-updated', $custumerNew->id);
        } else {
            Notification::make()
                ->title('Error')
                ->body('Existe un error contactese con el administradoor')
                ->danger()
                ->send();
        }


        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.customer.create-customer');
    }
}
