<?php

namespace App\Filament\Resources\Promotions\Tables;

use App\Models\Promotion;
use App\Models\Warehouse;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->label('Fecha inicio')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fecha fin')
                    ->date()
                    ->sortable(),
                TextColumn::make('discount_percentage')
                    ->label('Porcentage descuento')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('attach-asign')
                    ->label('Asignar productos')
                    ->icon('c-square-3-stack-3d')
                    ->url(fn (Promotion $record): string => route('filament.admin.resources.promotions.attach', ['record' => $record->id]))
                    ->color('success'),
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
