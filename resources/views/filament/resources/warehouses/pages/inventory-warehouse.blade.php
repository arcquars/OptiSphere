<x-filament-panels::page>
    <div class="preview bg-base-100 relative flex flex-wrap items-start gap-2 overflow-x-hidden bg-cover bg-top">
        <button class="btn btn-sm btn-primary">Ingresar productos</button>
        <button class="btn btn-sm btn-primary">Entregas a sucursales</button>
    </div>

    <livewire:warehouse.inventory-warehouse :warehouse-id="$warehouse->id" />

</x-filament-panels::page>
