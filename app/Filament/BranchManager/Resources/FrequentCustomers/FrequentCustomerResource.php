<?php

declare(strict_types=1);

namespace App\Filament\BranchManager\Resources\FrequentCustomers;

use App\Filament\BranchManager\Resources\FrequentCustomers\Pages\ListFrequentCustomers;
use App\Filament\Resources\FrequentCustomers\Tables\FrequentCustomersTable;
use App\Models\User;
use App\Services\FrequentCustomerService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FrequentCustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'fas-user-tag';

    protected static ?string $modelLabel = 'Cliente Frecuente';

    protected static ?string $pluralModelLabel = 'Clientes Frecuentes';

    protected static ?string $recordTitleAttribute = 'name';

    // Copia de solo consulta de App\Filament\Resources\FrequentCustomers\FrequentCustomerResource
    public static function table(Table $table): Table
    {
        return FrequentCustomersTable::configure($table, readOnly: true);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->role(FrequentCustomerService::ROLE);
    }

    // --------------------------------------------------------------
    // Solo lectura: sin crear, editar ni eliminar
    // --------------------------------------------------------------

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFrequentCustomers::route('/'),
        ];
    }
}
