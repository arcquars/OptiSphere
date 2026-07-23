<?php

declare(strict_types=1);

namespace App\Filament\BranchManager\Resources\FrequentCustomers\Pages;

use App\Filament\BranchManager\Resources\FrequentCustomers\FrequentCustomerResource;
use App\Models\Customer;
use App\Models\User;
use App\Services\FrequentCustomerService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListFrequentCustomers extends ListRecords
{
    protected static string $resource = FrequentCustomerResource::class;

    // Query string (no segmento de ruta): así Filament puede seguir generando
    // internamente la URL de esta página (breadcrumbs, etc.) sin requerirlo.
    #[Url]
    public ?int $branchId = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crear_cliente_frecuente')
                ->label('Crear Cliente Frecuente')
                ->modalHeading('Crear Cliente Frecuente')
                ->schema([
                    Select::make('customer_id')
                        ->label('Cliente vinculado')
                        ->helperText('Registro de cliente existente de esta sucursal al que se dará acceso al sistema.')
                        // Solo clientes de esta misma sucursal, sin usuario vinculado aún
                        ->options(fn (): array => Customer::query()
                            ->where('branch_id', $this->branchId)
                            ->whereNull('user_id')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required(),
                    TextInput::make('email')
                        ->label('Correo electronico')
                        ->email()
                        ->required()
                        ->unique(table: User::class),
                    TextInput::make('password')
                        ->label('Contrasenia')
                        ->password()
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Acceso activo')
                        ->default(true)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $customerId = $data['customer_id'] ?? null;

                    // Defensa: el cliente vinculado debe pertenecer a esta misma sucursal,
                    // aunque el Select ya solo ofrezca clientes de la sucursal en vista.
                    if ($customerId && ! Customer::where('id', $customerId)->where('branch_id', $this->branchId)->exists()) {
                        Notification::make()
                            ->title('El cliente seleccionado no pertenece a esta sucursal.')
                            ->danger()
                            ->send();

                        return;
                    }

                    unset($data['customer_id']);
                    $data['password'] = bcrypt($data['password']);

                    app(FrequentCustomerService::class)->create($data, $customerId ? (int) $customerId : null);

                    Notification::make()
                        ->title('Cliente frecuente creado')
                        ->success()
                        ->send();
                }),
        ];
    }

    // Acota el listado a los clientes frecuentes cuyo Customer pertenece a la sucursal
    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas(
                'customer',
                fn (Builder $query) => $query->where('branch_id', $this->branchId)
            ));
    }
}
