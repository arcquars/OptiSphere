<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Models\Warehouse;
use Filament\Actions\EditAction;
use \Filament\Resources\Pages\Page;

class ViewWarehouse extends Page
{
    protected static string $resource = WarehouseResource::class;

    protected static ?string $title = 'Inventario';

    protected string $view = 'filament.resources.warehouses.pages.view-warehouse';

    public Warehouse $warehouse;

    public function mount(int $warehouse_id): void
    {
        $this->warehouse = Warehouse::find($warehouse_id);
    }

    protected function getViewData(): array
    {
        return [
            'warehouse' => $this->warehouse,
//            'warehouseId' => 1,
        ];
    }
}
