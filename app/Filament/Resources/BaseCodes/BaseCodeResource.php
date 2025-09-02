<?php

namespace App\Filament\Resources\BaseCodes;

use App\Filament\Resources\BaseCodes\Pages\CreateBaseCode;
use App\Filament\Resources\BaseCodes\Pages\EditBaseCode;
use App\Filament\Resources\BaseCodes\Pages\ListBaseCodes;
use App\Filament\Resources\BaseCodes\Schemas\BaseCodeForm;
use App\Filament\Resources\BaseCodes\Tables\BaseCodesTable;
use App\Models\BaseCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BaseCodeResource extends Resource
{
    protected static ?string $model = BaseCode::class;

    protected static string|BackedEnum|null $navigationIcon = 'm-cog';

    protected static ?int $navigationSort = 5;

    protected static string | \UnitEnum | null $navigationGroup = 'Catálogos';

    protected static ?string $modelLabel = 'Código base';

    protected static ?string $pluralModelLabel = 'Códigos base';

    protected static ?string $recordTitleAttribute = 'BaseCode';

    public static function form(Schema $schema): Schema
    {
        return BaseCodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BaseCodesTable::configure($table);
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
            'index' => ListBaseCodes::route('/'),
            'create' => CreateBaseCode::route('/create'),
            'edit' => EditBaseCode::route('/{record}/edit'),
        ];
    }
}
