<?php

namespace App\Filament\Resources\ConfiguracionBancos\Pages;

use App\Filament\Resources\ConfiguracionBancos\ConfiguracionBancoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConfiguracionBanco extends EditRecord
{
    protected static string $resource = ConfiguracionBancoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
