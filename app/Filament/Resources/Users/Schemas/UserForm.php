<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electronico')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label('Contrasenia')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->visible(fn (string $context): bool => $context === 'create' || $context === 'edit'),
                Select::make('roles')
                    ->label('Roles')
                    ->multiple()
                    ->relationship('roles', 'name') // Relación con el modelo de roles de Spatie
                    ->options(Role::all()->pluck('name', 'id'))
                    ->required(),
                Select::make('branches')
                    ->label('Sucursales')
                    ->multiple()
                    ->relationship('branches', 'name')
                    ->options(Branch::all()->pluck('name', 'id')),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
            ]);
    }
}
