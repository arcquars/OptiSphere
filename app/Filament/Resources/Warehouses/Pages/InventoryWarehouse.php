<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Models\Warehouse;
use Filament\Resources\Pages\Page;

class InventoryWarehouse extends Page
{
    protected static string $resource = WarehouseResource::class;

    protected static ?string $title = 'Inventario';

    protected string $view = 'filament.resources.warehouses.pages.inventory-warehouse';

    public Warehouse $warehouse;

    public function mount(int|string $warehouse_id): void
    {
        $this->warehouse = Warehouse::find($warehouse_id);
    }
}
