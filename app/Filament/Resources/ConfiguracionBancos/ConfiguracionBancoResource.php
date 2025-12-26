<?php

namespace App\Filament\Resources\ConfiguracionBancos;

use App\Filament\Resources\ConfiguracionBancos\Pages\CreateConfiguracionBanco;
use App\Filament\Resources\ConfiguracionBancos\Pages\EditConfiguracionBanco;
use App\Filament\Resources\ConfiguracionBancos\Pages\ListConfiguracionBancos;
use App\Filament\Resources\ConfiguracionBancos\Schemas\ConfiguracionBancoForm;
use App\Filament\Resources\ConfiguracionBancos\Tables\ConfiguracionBancosTable;
use App\Models\ConfiguracionBanco;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ConfiguracionBancoResource extends Resource
{
    protected static ?string $model = ConfiguracionBanco::class;

    protected static string|BackedEnum|null $navigationIcon = "fas-coins";

    protected static string | \UnitEnum | null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Cuenta Banco';

    protected static ?string $pluralModelLabel = 'Configuraciones Banco';

    protected static ?string $recordTitleAttribute = 'codigo_empresa';

    public static function form(Schema $schema): Schema
    {
        return ConfiguracionBancoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConfiguracionBancosTable::configure($table);
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
            'index' => ListConfiguracionBancos::route('/'),
            'create' => CreateConfiguracionBanco::route('/create'),
            'edit' => EditConfiguracionBanco::route('/{record}/edit'),
        ];
    }
}
