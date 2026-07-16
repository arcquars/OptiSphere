<?php

namespace App\Filament\FrequentCustomer\Resources\SaleHistory\Pages;

use App\Filament\FrequentCustomer\Resources\SaleHistory\SaleHistoryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSaleHistory extends ViewRecord
{
    protected static string $resource = SaleHistoryResource::class;

    // Solo lectura: sin acciones de edición en la cabecera
    protected function getHeaderActions(): array
    {
        return [];
    }
}
