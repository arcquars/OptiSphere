<?php

namespace App\Filament\Resources\BaseCodes\Pages;

use App\Filament\Resources\BaseCodes\BaseCodeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBaseCode extends EditRecord
{
    protected static string $resource = BaseCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
