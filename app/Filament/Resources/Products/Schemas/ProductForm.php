<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\BaseCode;
use App\Models\Product;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

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
                            ->required()
                            ->rule(
                                Rule::unique('products', 'code')
                                    ->ignore(fn (?Product $record): ?Product => $record)
                                    ->withoutTrashed()
                            ),
                        Select::make('supplier_id')
                            ->relationship(name: 'supplier', titleAttribute: 'name')
                            ->searchable(['name'])
                            ->preload(),
                        Toggle::make('is_active')
                            ->default(true)
                            ->required()
                            ->disabled(
                                fn (Get $get): bool =>
                                !empty($get('has_optical_properties'))
                            ),
                        FileUpload::make('image_path')
                            ->image()
                            ->disk('public')
                            ->directory('product-attachments')
                            ->visibility('public')
                            ->required()->columnSpan(2),
                        Textarea::make('description')
                            ->required()
                            ->columnSpan(2),

                        ])->columns(4),
                Select::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->label('Categorías')
                    ->columnSpan('full'),
                Checkbox::make('has_optical_properties')
                    ->label('¿Agregar Propiedades Ópticas?')
                    ->live() // 'reactive()' se llama 'live()' en v4
                    // Este hook personaliza cómo se "hidrata" (carga) el estado
                    // del checkbox al abrir la página de "Editar".
                    ->afterStateHydrated(function (Component $component, ?Product $record) {
                        // $record es el Product que se está editando.
                        if ($record === null) {
                            return; // Estamos en "Crear", no hacer nada.
                        }

                        // Esta es la consulta Eloquent
                        if ($record->opticalProperties()->exists()) {
                            // Usamos $component->state() para establecer el estado.
                            $component->state(true);
                        }
                    }),
                Section::make('Propiedades Ópticas')
                    ->visible(fn (Get $get) => $get('has_optical_properties'))
                    ->schema([
                        // Agrega el Fieldset para la tabla optical_properties
                        Fieldset::make('Propiedades Opticas')
                            ->schema([
                                Select::make('base_code')
                                    ->label('Código Base')
                                    ->options(BaseCode::all()->pluck('name', 'name'))
                                    ->required()
                                    ->searchable(),
                                Radio::make('type')
                                    ->label('Tipo?')
                                    ->options([
                                        '+' => 'Positivo',
                                        '-' => 'Negativo',
                                    ])
                                    ->default('+')
                                    ->inline(),
                                TextInput::make('sphere')
                                    ->label('Esfera')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('cylinder')
                                    ->label('Cilindro')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->relationship('opticalProperties'),
                    ]),

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
