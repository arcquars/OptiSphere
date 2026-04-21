<?php

namespace App\Filament\BranchManager\Resources\CashMovements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CashMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('branch.name')
                    ->label('Sucursal'),
                TextEntry::make('user.name')
                    ->label('Usuario'),
                TextEntry::make('cash_box_closing_id')
                    ->label('Cierre de Caja')
                    ->numeric(),
                TextEntry::make('type')
                    ->label('Tipo'),
                TextEntry::make('amount')
                    ->label('Monto')
                    ->numeric(),
                TextEntry::make('description')
                    ->label('Descripción'),
                TextEntry::make('created_at')
                    ->label('Creado en')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('Actualizado en')
                    ->dateTime(),
            ]);
    }
}
