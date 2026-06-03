<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use Filament\Resources\Pages\Page;

class SortUsers extends Page
{
    protected static string $resource = WarehouseResource::class;

    protected string $view = 'filament.resources.warehouses.pages.sort-users';
}
