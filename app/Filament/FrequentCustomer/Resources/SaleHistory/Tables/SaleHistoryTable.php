<?php

namespace App\Filament\FrequentCustomer\Resources\SaleHistory\Tables;

use App\Models\Sale;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SaleHistoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date_sale')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Sucursal'),
                TextColumn::make('final_total')
                    ->label('Total')
                    ->money('BOB'),
                TextColumn::make('paid_amount')
                    ->label('Pagado')
                    ->money('BOB'),
                TextColumn::make('due_amount')
                    ->label('Saldo')
                    ->money('BOB'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Sale::SALE_STATUS_PAID => 'Pagado',
                        Sale::SALE_STATUS_PARTIAL_PAYMENT => 'Pago parcial',
                        Sale::SALE_STATUS_CREDIT => 'Crédito',
                        Sale::SALE_STATUS_VOIDED => 'Anulado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Sale::SALE_STATUS_PAID => 'success',
                        Sale::SALE_STATUS_PARTIAL_PAYMENT => 'warning',
                        Sale::SALE_STATUS_CREDIT => 'info',
                        Sale::SALE_STATUS_VOIDED => 'danger',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
