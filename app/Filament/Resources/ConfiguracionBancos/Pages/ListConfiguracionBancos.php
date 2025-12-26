<?php

namespace App\Filament\Resources\ConfiguracionBancos\Pages;

use App\Filament\Resources\ConfiguracionBancos\ConfiguracionBancoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConfiguracionBancos extends ListRecords
{
    protected static string $resource = ConfiguracionBancoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
