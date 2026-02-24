<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use Filament\Resources\Pages\Page;

class HistoryShow extends Page
{
    protected static ?string $title = 'Ver Historial de Movimiento';
    protected static string $resource = WarehouseResource::class;

    protected string $view = 'filament.resources.warehouses.pages.history-show';
}
