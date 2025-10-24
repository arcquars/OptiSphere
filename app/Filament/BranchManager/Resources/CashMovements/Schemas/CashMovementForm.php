<?php

namespace App\Filament\BranchManager\Resources\CashMovements\Schemas;

use App\Models\Branch;
use App\Models\CashMovement;
use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CashMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->label('Sucursal')
                    ->options(fn () => self::getAvailableBranches())
                    ->required(),
                Select::make('user_id')
                    ->label('Usuario')
                    ->searchable()
                    ->options(fn () => self::userOptions())
                    ->default(fn () => auth()->id())
                    ->visible(fn () => self::isAdmin())
                    ->required(),

                // --- No admins: ver su nombre, pero no cambiar ---
                Placeholder::make('user_name')
                    ->label('Usuario')
                    ->content(fn () => auth()->user()?->name)
                    ->visible(fn () => ! self::isAdmin()),
                // Campo real que se guarda cuando no es admin
                Hidden::make('user_id')
                    ->default(fn () => auth()->id())
                    ->visible(fn () => ! self::isAdmin()),
                Select::make('type')
                    ->label('Tipo')
                    ->options([CashMovement::TYPE_INCOME => __('cerisier.'.CashMovement::TYPE_INCOME), CashMovement::TYPE_EXPENSE => __('cerisier.'.CashMovement::TYPE_EXPENSE)])
                    ->required(),
                TextInput::make('amount')
                    ->label('Cantidad')
                    ->required()
                    ->numeric(),
                TextInput::make('description')
                    ->label('Descripcion')
                    ->required(),
            ]);
    }

    protected static function getAvailableBranches(): array
    {
        $user = Auth::user();

        $query = Branch::query();

        if ($user->hasRole('admin')) {
            // Mostrar todas las sucursales
            return $query->pluck('name', 'id')->toArray();
        }

        // Solo las sucursales asignadas al usuario
        return $user->branches()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected static function userOptions(): array
    {
        return User::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected static function isAdmin(): bool
    {
        $user = auth()->user();
        // Ajusta a tu sistema de roles/permisos:
        return $user?->hasRole('admin') ?? false;
    }
}
