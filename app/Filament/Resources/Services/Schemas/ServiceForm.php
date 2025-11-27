<?php

namespace App\Filament\Resources\Services\Schemas;


use App\Models\Price;
use App\Models\SiatDataActividad;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Principal')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre del Servicio'),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->label('Código / SKU')
                            ->unique(
                                table: 'services',
                                column: 'name',
                                ignoreRecord: true
                            ),

                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->label('Descripción'),

                        // 1. CAMPO PARA LA IMAGEN DEL SERVICIO
                        FileUpload::make('path_image')
                            ->label('Imagen')
                            ->image()
                            ->directory('services-images') // Directorio donde se guardarán las imágenes
                            ->visibility('public') // O 'private' si lo prefieres
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->label('Activo'),
                    ])
                    ->columns(2),

                Section::make('Clasificación')
                    ->schema([
                        // 2. CAMPO PARA LA RELACIÓN POLIMÓRFICA CON CATEGORÍAS
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->label('Categorías')
                    ]),
                Section::make('SIAT')
                    ->schema([
                        Select::make('siat_data_actividad_code')
                            ->options(
                        SiatDataActividad::all()->pluck('descripcion', 'codigo')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('siat_data_product_code', null))
                            ->label('Actividad SIAT'),
                        Select::make('siat_data_product_code')
                            // 🚨 CAMBIO CLAVE 2: Aquí es donde se define la relación y el filtro
                            // modifyQueryUsing se aplica *dentro* del método relationship()
                            ->relationship(
                                name: 'siatDataProducto', 
                                titleAttribute: 'descripcion_producto',
                                // El tercer parámetro del relationship es donde se define el callback de filtrado
                                modifyQueryUsing: fn (Builder $query, Get $get) => $query->where(
                                    // 💡 IMPORTANTE: 'codigo_actividad_relacion' debe ser la columna real en la tabla de productos
                                    'codigo_actividad', 
                                    $get('siat_data_actividad_code')
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->label('Producto SIAT')
                            ->disabled(fn (Get $get): bool => empty($get('siat_data_actividad_code'))),
                    ]),
            ]);
    }
}
