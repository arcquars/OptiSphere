<?php

namespace App\Filament\Resources\Services\Schemas;


use App\Models\Price;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Principal')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
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
                            )
                            ->withoutTrashed(),

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
                            ->label('Categorías'),
                    ]),
            ]);
    }
}
