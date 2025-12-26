<?php

namespace App\Filament\Resources\ConfiguracionBancos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConfiguracionBancosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('user_name')
                    ->label('Usuario')
                    ->searchable(),
                TextColumn::make('api_key')
                    ->searchable(),
                TextColumn::make('nombre_empresa')
                    ->searchable(),
                TextColumn::make('codigo_empresa')
                    ->label('Código empresa')
                    ->searchable(),
                IconColumn::make('activo')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
