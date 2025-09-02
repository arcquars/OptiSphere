<?php

namespace App\Filament\Resources\BaseCodes\Pages;

use App\Filament\Resources\BaseCodes\BaseCodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBaseCodes extends ListRecords
{
    protected static string $resource = BaseCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
