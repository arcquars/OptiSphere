<?php

namespace App\Filament\FrequentCustomer\Resources\SaleHistory\Schemas;

use App\Models\Sale;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleHistoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la compra')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('date_sale')
                            ->label('Fecha')
                            ->date(),
                        TextEntry::make('branch.name')
                            ->label('Sucursal'),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Sale::SALE_STATUS_PAID => 'Pagado',
                                Sale::SALE_STATUS_PARTIAL_PAYMENT => 'Pago parcial',
                                Sale::SALE_STATUS_CREDIT => 'Crédito',
                                Sale::SALE_STATUS_VOIDED => 'Anulado',
                                default => $state,
                            }),
                        TextEntry::make('payment_method')
                            ->label('Método de pago'),
                        TextEntry::make('final_total')
                            ->label('Total')
                            ->money('BOB'),
                        TextEntry::make('paid_amount')
                            ->label('Pagado')
                            ->money('BOB'),
                        TextEntry::make('due_amount')
                            ->label('Saldo pendiente')
                            ->money('BOB'),
                    ]),
                Section::make('Detalle')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Productos y servicios')
                            ->columns(4)
                            ->schema([
                                TextEntry::make('salable.name')
                                    ->label('Descripción'),
                                TextEntry::make('type_label')
                                    ->label('Tipo'),
                                TextEntry::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric(),
                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('BOB'),
                            ]),
                    ]),
            ]);
    }
}
