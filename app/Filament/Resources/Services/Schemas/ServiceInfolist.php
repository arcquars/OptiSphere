<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label('Nombre del Servicio'),
                TextEntry::make('code')->label('Código / SKU'),
                ImageEntry::make('path_image')->label('Imagen'),

                RepeatableEntry::make('categories')->label('Categorías')
                    ->schema([
                        TextEntry::make('name')->label('Nombre'),
                    ])
                    ->columns(2),

                IconEntry::make('is_active')->label('Activo')
                    ->boolean(),
//                TextEntry::make('created_at')
//                    ->dateTime(),
//                TextEntry::make('updated_at')
//                    ->dateTime(),
            ]);
    }
}
