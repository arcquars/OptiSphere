<?php

namespace App\Filament\BranchManager\Resources\CashMovements;

use App\Filament\BranchManager\Resources\CashMovements\Pages\CreateCashMovement;
use App\Filament\BranchManager\Resources\CashMovements\Pages\EditCashMovement;
use App\Filament\BranchManager\Resources\CashMovements\Pages\ListCashMovements;
use App\Filament\BranchManager\Resources\CashMovements\Pages\ViewCashMovement;
use App\Filament\BranchManager\Resources\CashMovements\Schemas\CashMovementForm;
use App\Filament\BranchManager\Resources\CashMovements\Schemas\CashMovementInfolist;
use App\Filament\BranchManager\Resources\CashMovements\Tables\CashMovementsTable;
use App\Models\CashMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashMovementResource extends Resource
{
    protected static ?string $model = CashMovement::class;

    protected static string|BackedEnum|null $navigationIcon = 'fas-store';

    protected static ?string $modelLabel = 'Movimiento de Caja';

    protected static ?string $pluralModelLabel = 'Movimientos de Caja';

    protected static ?string $recordTitleAttribute = 'CashMovement';

    public static function form(Schema $schema): Schema
    {
        return CashMovementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CashMovementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashMovementsTable::configure($table);
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
            'index' => ListCashMovements::route('/'),
            'create' => CreateCashMovement::route('/create'),
            'view' => ViewCashMovement::route('/{record}'),
            'edit' => EditCashMovement::route('/{record}/edit'),
        ];
    }
}
