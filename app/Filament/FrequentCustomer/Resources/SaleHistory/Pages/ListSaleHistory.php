<?php

namespace App\Filament\FrequentCustomer\Resources\SaleHistory\Pages;

use App\Filament\FrequentCustomer\Resources\SaleHistory\SaleHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListSaleHistory extends ListRecords
{
    protected static string $resource = SaleHistoryResource::class;

    // Solo lectura: sin acción de creación en la cabecera
    protected function getHeaderActions(): array
    {
        return [];
    }
}
