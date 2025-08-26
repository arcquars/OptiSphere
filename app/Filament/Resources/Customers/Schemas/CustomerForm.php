<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombres')
                    ->required(),
                TextInput::make('nit')->label('NIT')->unique(),
                TextInput::make('address')->label('Dirección'),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email(),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel(),
                TextInput::make('contact_info')
                    ->label('Información de contacto')
                    ->required(),
                Toggle::make('can_buy_on_credit')
                    ->label('Credito')
                    ->required(),
                Select::make('type')
                    ->label('Tipo')
                    ->options(['normal' => 'Normal', 'especial' => 'Especial', 'mayorista' => 'Mayorista'])
                    ->default('normal')
                    ->required(),
            ]);
    }
}
