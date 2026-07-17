<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductAuthentications;

use App\Filament\Resources\ProductAuthentications\Pages\ListProductAuthentications;
use App\Filament\Resources\ProductAuthentications\Tables\ProductAuthenticationsTable;
use App\Models\ProductAuthentication;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ProductAuthenticationResource extends Resource
{
    protected static ?string $model = ProductAuthentication::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string | \UnitEnum | null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Producto Autentificado';

    protected static ?string $pluralModelLabel = 'Aprobar Productos Autentificados';

    protected static ?string $recordTitleAttribute = 'cliente';

    public static function table(Table $table): Table
    {
        return ProductAuthenticationsTable::configure($table);
    }

    /**
     * La aprobación de autenticaciones es exclusiva del Administrador dentro del
     * panel de administración. Aunque el panel branch-coordinator comparta la
     * carpeta de descubrimiento de recursos, este solo se expone en /admin.
     */
    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin'
            && Auth::user()?->hasRole('admin') === true;
    }

    /**
     * Orden estricto: primero las pendientes de aprobación (is_authentication = false)
     * y, dentro de ellas, las solicitudes más recientes arriba.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'frequentCustomer.user'])
            ->orderBy('is_authentication', 'asc')
            ->orderBy('created_at', 'desc');
    }

    // --------------------------------------------------------------
    // Solo lectura y aprobación rápida: sin crear, editar ni eliminar
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
