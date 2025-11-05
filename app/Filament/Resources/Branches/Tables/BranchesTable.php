<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Filament\Resources\Branches\Pages\CashBoxReport;
use App\Filament\Resources\Branches\Pages\InventoryBranch;
use App\Filament\Resources\Branches\Pages\InvoiceConfig;
use App\Filament\Resources\Branches\Pages\ManageBranch;
use App\Filament\Tables\Columns\BranchActionsColumn;
use App\Models\Branch;
use App\Models\Warehouse;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Dirección')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    // BranchActionsColumn::make('actions'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('cash-box-closing')
                    ->label('')
                    ->tooltip('Flujo de caja') 
                    ->icon('fas-cash-register')
                    ->url(fn (Branch $record) => CashBoxReport::getUrl(['branch_id' => $record->id]))
                    ->color('success'),
                Action::make('manage-branch')
                    ->label('')
                    ->tooltip('Matriz')
                    ->icon('fas-table-cells-large')
                    ->url(fn (Branch $record) => ManageBranch::getUrl(['branch_id' => $record->id]))
                    ->color('success'),
                Action::make('inventory-branch')
                    ->label('')
                    ->tooltip('Inventario')
                    ->icon('fas-table-list')
                    ->url(fn (Branch $record) => InventoryBranch::getUrl(['branch_id' => $record->id]))
                    ->color('success'),
                Action::make('invoice-config')
                    ->label('')
                    ->tooltip('Configurar SIAT') 
                    ->icon('fas-file-invoice')
                    ->url(fn (Branch $record) => InvoiceConfig::getUrl(['branch_id' => $record->id]))
                    ->color('info'),
                EditAction::make()
                    ->label('')
                    ->tooltip('Editar')
                    ->icon('fas-pen-to-square'),
            ])
            ->toolbarActions([
//                BulkActionGroup::make([
//                    DeleteBulkAction::make(),
//                ]),
            ]);
    }
}
