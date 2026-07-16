<?php

namespace App\Filament\Resources\FrequentCustomers;

use App\Filament\Resources\FrequentCustomers\Pages\CreateFrequentCustomer;
use App\Filament\Resources\FrequentCustomers\Pages\EditFrequentCustomer;
use App\Filament\Resources\FrequentCustomers\Pages\ListFrequentCustomers;
use App\Filament\Resources\FrequentCustomers\Schemas\FrequentCustomerForm;
use App\Filament\Resources\FrequentCustomers\Tables\FrequentCustomersTable;
use App\Models\User;
use App\Services\FrequentCustomerService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Table;

class FrequentCustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'fas-user-tag';

    protected static string | \UnitEnum | null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Cliente Frecuente';

    protected static ?string $pluralModelLabel = 'Clientes Frecuentes';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FrequentCustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FrequentCustomersTable::configure($table);
    }

    /**
     * La gestión de clientes frecuentes es exclusiva del Administrador dentro
     * del panel de administración. Aunque otros paneles compartan la carpeta de
     * descubrimiento de recursos, este solo se registra/expone en /admin para admins.
     */
    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin'
            && Auth::user()?->hasRole('admin') === true;
    }

    /**
     * Acota el recurso únicamente a los usuarios con rol de cliente frecuente,
     * evitando exponer o alterar el resto de usuarios del sistema.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->role(FrequentCustomerService::ROLE);
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
            'create' => CreateFrequentCustomer::route('/create'),
            'edit' => EditFrequentCustomer::route('/{record}/edit'),
        ];
    }
}
