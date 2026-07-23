<?php

declare(strict_types=1);

namespace App\Filament\BranchManager\Resources\ProductAuthentications;

use App\Filament\BranchManager\Resources\ProductAuthentications\Pages\ListProductAuthentications;
use App\Filament\Resources\ProductAuthentications\Tables\ProductAuthenticationsTable;
use App\Models\ProductAuthentication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductAuthenticationResource extends Resource
{
    protected static ?string $model = ProductAuthentication::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $modelLabel = 'Producto Autentificado';

    protected static ?string $pluralModelLabel = 'Productos Autentificados';

    protected static ?string $recordTitleAttribute = 'cliente';

    // Copia de solo consulta de App\Filament\Resources\ProductAuthentications\ProductAuthenticationResource,
    // sin la columna de aprobación (ver ListProductAuthentications::table()).
    public static function table(Table $table): Table
    {
        return ProductAuthenticationsTable::configure($table, includeApprovalColumn: false);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'frequentCustomer.user'])
            ->orderBy('is_authentication', 'asc')
            ->orderBy('created_at', 'desc');
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
            'index' => ListProductAuthentications::route('/'),
        ];
    }
}
