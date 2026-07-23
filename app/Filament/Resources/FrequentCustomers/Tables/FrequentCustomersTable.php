<?php

namespace App\Filament\Resources\FrequentCustomers\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FrequentCustomersTable
{
    /**
     * @param bool $readOnly Sin acciones de edición/eliminación. El panel branch-manager
     *                       reutiliza esta misma tabla en modo solo consulta.
     */
    public static function configure(Table $table, bool $readOnly = false): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo electronico')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Cliente vinculado')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Acceso activo')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions($readOnly ? [] : [
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
