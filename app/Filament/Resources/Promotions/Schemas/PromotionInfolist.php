<?php

namespace App\Filament\Resources\Promotions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PromotionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('discount_percentage')
                    ->label('Descuento %')
                    ->numeric(),
                TextEntry::make('start_date')
                    ->label('Fecha inicio')
                    ->dateTime(),
                TextEntry::make('end_date')
                    ->label('Fecha Fin')
                    ->dateTime(),
                IconEntry::make('is_active')
                    ->label('Activo')
                    ->boolean()
            ]);
    }
}
