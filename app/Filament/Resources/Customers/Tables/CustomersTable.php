<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->label('Nombres')
                    ->searchable(),
                TextColumn::make('nit')
                    ->label('NIT')
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Dirección')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                TextColumn::make('contact_info')
                    ->label('Información de contacto')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->searchable(),
                IconColumn::make('can_buy_on_credit')
                    ->label('Crédito')
                    ->boolean(),
                TextColumn::make('type')->label('Tipo'),
                
            ])
            ->filters([
                SelectFilter::make('branch_id')
                ->label('Sucursal')
                ->relationship('branch', 'name')
                        ])
            ->recordActions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Editar')
                    ->icon('fas-pen-to-square')
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
