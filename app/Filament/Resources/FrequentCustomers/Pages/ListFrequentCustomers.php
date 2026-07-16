<?php

namespace App\Filament\Resources\FrequentCustomers\Pages;

use App\Filament\Resources\FrequentCustomers\FrequentCustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFrequentCustomers extends ListRecords
{
    protected static string $resource = FrequentCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
