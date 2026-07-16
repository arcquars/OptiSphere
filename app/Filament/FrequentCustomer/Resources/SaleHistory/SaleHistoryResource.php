<?php

namespace App\Filament\FrequentCustomer\Resources\SaleHistory;

use App\Filament\FrequentCustomer\Resources\SaleHistory\Pages\ListSaleHistory;
use App\Filament\FrequentCustomer\Resources\SaleHistory\Pages\ViewSaleHistory;
use App\Filament\FrequentCustomer\Resources\SaleHistory\Schemas\SaleHistoryInfolist;
use App\Filament\FrequentCustomer\Resources\SaleHistory\Tables\SaleHistoryTable;
use App\Models\Sale;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Table;

class SaleHistoryResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static string|BackedEnum|null $navigationIcon = 'fas-receipt';

    protected static ?string $modelLabel = 'Venta';

    protected static ?string $pluralModelLabel = 'Mis Compras';

    protected static ?string $slug = 'compras';

    protected static ?string $recordTitleAttribute = 'id';

    public static function infolist(Schema $schema): Schema
    {
        return SaleHistoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SaleHistoryTable::configure($table);
    }

    /**
     * Restringe el historial a las ventas del cliente vinculado al usuario
     * autenticado. Si el usuario no tiene cliente vinculado, no ve nada.
     */
    public static function getEloquentQuery(): Builder
    {
        $customerId = Auth::user()?->customer?->id;

        return parent::getEloquentQuery()
            ->where('customer_id', $customerId ?? 0)
            ->with(['branch'])
            ->latest('date_sale');
    }

    // --------------------------------------------------------------
    // Solo lectura: el cliente frecuente no puede crear ni modificar
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

    public static function getPages(): array
    {
        return [
            'index' => ListSaleHistory::route('/'),
            'view' => ViewSaleHistory::route('/{record}'),
        ];
    }
}
