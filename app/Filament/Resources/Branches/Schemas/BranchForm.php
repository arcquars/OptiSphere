<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('address')
                    ->label('Direccion')
                    ->required(),
                Select::make('configuracion_banco_id')
                    ->label('Configuración de Banco (Pagos QR)')
                    ->placeholder('Seleccione una cuenta bancaria')
                    // Definimos la relación: nombre del método en el modelo y columna a mostrar
                    ->relationship(
                        name: 'configuracionBanco', 
                        titleAttribute: 'nombre_empresa',
                        // Opcional: Filtramos solo los bancos activos usando el scope del modelo
                        modifyQueryUsing: fn (Builder $query) => $query->activo()
                    )
                    ->searchable() // Permite buscar por nombre
                    ->preload()    // Carga las opciones al abrir para mejorar UX
                    ->nullable(),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
            ]);
    }
}
