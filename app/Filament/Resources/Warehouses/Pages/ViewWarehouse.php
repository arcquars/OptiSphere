<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use Filament\Actions\EditAction;
use \Filament\Resources\Pages\Page;

class ViewWarehouse extends Page
{
    protected static string $resource = WarehouseResource::class;

    protected static ?string $title = 'Inventario';

    protected string $view = 'filament.resources.warehouses.pages.view-warehouse';
}
