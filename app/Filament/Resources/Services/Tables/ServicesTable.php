<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre del Servicio')
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Código / SKU')
                    ->searchable(),
                ImageColumn::make('path_image')
                    ->label('Imagen'),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('categories.name')
                    ->label('Categorías')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->categories->pluck('name')->join(', ');
                    })
                    ->badge() // opcional: muestra como badges
                    ->sortable(false)
                    ->searchable(false)
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
