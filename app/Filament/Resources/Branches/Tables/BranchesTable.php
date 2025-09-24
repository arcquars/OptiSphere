<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Filament\Resources\Branches\Pages\InventoryBranch;
use App\Filament\Resources\Branches\Pages\ManageBranch;
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
                    ->label('Direccion')
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
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('manage-branch')
                    ->label('Matriz')
                    ->icon('c-square-3-stack-3d')
                    ->url(fn (Branch $record) => ManageBranch::getUrl(['branch_id' => $record->id]))
                    ->color('success'),
                Action::make('inventory-branch')
                    ->label('Inventario')
                    ->icon('c-square-3-stack-3d')
                    ->url(fn (Branch $record) => InventoryBranch::getUrl(['branch_id' => $record->id]))
                    ->color('success'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
