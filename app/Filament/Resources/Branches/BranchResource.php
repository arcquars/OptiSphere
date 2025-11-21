<?php

namespace App\Filament\Resources\Branches;

use App\Filament\Resources\Branches\Pages\CashBoxReport;
use App\Filament\Resources\Branches\Pages\CashBoxView;
use App\Filament\Resources\Branches\Pages\CreateBranch;
use App\Filament\Resources\Branches\Pages\EditBranch;
use App\Filament\Resources\Branches\Pages\InventoryBranch;
use App\Filament\Resources\Branches\Pages\InvoiceConfig;
use App\Filament\Resources\Branches\Pages\ListBranches;
use App\Filament\Resources\Branches\Pages\ManageBranch;
use App\Filament\Resources\Branches\Pages\SiatManager;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use App\Filament\Resources\Branches\Tables\BranchesTable;
use App\Models\Branch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|BackedEnum|null $navigationIcon = 'fas-store';

    protected static string | \UnitEnum | null $navigationGroup = 'Catálogos';

    protected static ?string $modelLabel = 'Sucursal';

    protected static ?string $pluralModelLabel = 'Sucursales';

    protected static ?string $recordTitleAttribute = 'Branch';

    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
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
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'edit' => EditBranch::route('/{record}/edit'),
            'matrix' => ManageBranch::route('/{branch_id}/matrix'),
            'inventory' => InventoryBranch::route('/{branch_id}/inventory'),
            'cash-box-report' => CashBoxReport::route('/{branch_id}/cash-box'),
            'cash-box-view' => CashBoxView::route('/cash-box-report/{cashBoxClosingId}/cash-box-view'),
            'invoice-config' => InvoiceConfig::route('/{branch_id}/siat/invoice-config'),
            'siat-manager' => SiatManager::route('/{branch_id}/siat/siat-manager'),
        ];
    }
}
