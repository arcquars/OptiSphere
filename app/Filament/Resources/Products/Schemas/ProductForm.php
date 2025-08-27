<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Producto')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('code')
                            ->required(),
//                        TextInput::make('supplier_id')
//                            ->required()
//                            ->numeric(),
                        Select::make('supplier_id')
                            ->relationship(name: 'supplier', titleAttribute: 'name')
                            ->searchable(['name'])
                            ->preload(),
                        Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                        FileUpload::make('image_path')
                            ->image()
                            ->required()->columnSpan(2),
                        Textarea::make('description')
                            ->required()
                            ->columnSpan(2),

                        ])->columns(4),
                // Agrega el Fieldset para la tabla optical_properties
                Fieldset::make('Propiedades Opticas')
                    ->schema([
                        TextInput::make('sphere')
                            ->label('Esfera')
                            ->numeric()
                            ->required(),
                        TextInput::make('cylinder')
                            ->label('Cilindro')
                            ->numeric()
                            ->required(),
                        TextInput::make('axis')
                            ->label('Eje')
                            ->integer()
                            ->required(),
                        TextInput::make('add')
                            ->label('Adicion')
                            ->numeric()
                            ->required(),
                    ])
                    ->relationship('opticalProperties'),
                // Repeater para los diferentes tipos de precios
                // Repeater para los diferentes tipos de precios
                Repeater::make('prices')
                    ->label('Precios por Tipo de Cliente')
                    ->relationship('prices')
                    ->schema([
                        Select::make('type')
                            ->label('Tipo de cliente')
                            ->options([
                                'normal' => 'Normal',
                                'especial' => 'Especial',
                                'mayorista' => 'Mayorista',
                            ])
                            ->required(),
//                            ->unique(ignoreRecord: true), // Asegura que cada tipo de cliente sea único
                        TextInput::make('price')
                            ->label('Precio')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(2) // Muestra los campos en dos columnas
                    ->collapsible() // Permite colapsar los bloques de precio
                    ->defaultItems(3) // Genera 3 campos por defecto (uno para cada tipo)
                    ->minItems(3) // Hace que los 3 elementos sean obligatorios
                    ->maxItems(3), // No permite agregar más de 3 elementos
            ])->columns(1);
    }
}
