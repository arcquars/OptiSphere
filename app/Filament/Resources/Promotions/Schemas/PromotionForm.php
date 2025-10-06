<?php

namespace App\Filament\Resources\Promotions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                Textarea::make('description')
                    ->label('Descripcion')
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->label('Fecha inicio')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Fecha fin')
                    ->required(),
                TextInput::make('discount_percentage')
                    ->label('Porcentage descuento')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Toggle::make('is_active')
                    ->label('Activo'),
            ]);
    }
}
