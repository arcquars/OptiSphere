<x-filament-panels::page>
    <div class="preview bg-base-100 relative flex flex-wrap items-start gap-2 overflow-x-hidden bg-cover bg-top">
        <livewire:warehouse.product-entry :warehouseId="$warehouse->id" />
    </div>

    <livewire:warehouse.inventory-warehouse :warehouseId="$warehouse->id" />

</x-filament-panels::page>
