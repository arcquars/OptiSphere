<?php

namespace App\Filament\Resources\BaseCodes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BaseCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->unique()
                    ->required(),
            ]);
    }
}
