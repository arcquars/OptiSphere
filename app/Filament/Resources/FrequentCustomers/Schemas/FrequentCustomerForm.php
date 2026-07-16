<?php

namespace App\Filament\Resources\FrequentCustomers\Schemas;

use App\Models\Customer;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FrequentCustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Cliente vinculado')
                    ->helperText('Registro de cliente existente al que se dará acceso al sistema.')
                    // Solo clientes sin usuario, más el ya vinculado a este registro (en edición)
                    ->options(function (?User $record): array {
                        return Customer::query()
                            ->where(function ($query) use ($record): void {
                                $query->whereNull('user_id');
                                if ($record !== null) {
                                    $query->orWhere('user_id', $record->id);
                                }
                            })
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->required(),
                    // Nota: no es columna de users; la página lo extrae del estado
                    // del formulario y lo delega al FrequentCustomerService.
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electronico')
                    ->email()
                    ->required()
                    ->unique(table: User::class, ignoreRecord: true),
                TextInput::make('password')
                    ->label('Contrasenia')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Toggle::make('is_active')
                    ->label('Acceso activo')
                    ->default(true)
                    ->required(),
            ]);
    }
}
