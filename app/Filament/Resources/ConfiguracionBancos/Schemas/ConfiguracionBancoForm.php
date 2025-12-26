<?php

namespace App\Filament\Resources\ConfiguracionBancos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ConfiguracionBancoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_name')
                    ->label("Usuario")
                    ->required(),
                TextInput::make('password')
                    ->label("Contraseña")
                    ->required(),
                TextInput::make('numero_cuenta')
                    ->label("Número de cuenta")
                    ->required(),
                TextInput::make('api_key')
                    ->required(),
                TextInput::make('nombre_empresa')
                    ->required(),
                TextInput::make('codigo_empresa')
                    ->label("Código empresa")
                    ->unique(column: 'codigo_empresa')
                    ->required(),
                Toggle::make('activo')
                    ->required(),
            ]);
    }
}
