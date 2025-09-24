<x-filament-panels::page>

    <div class="preview bg-base-100 relative flex flex-wrap items-start gap-2 overflow-x-hidden bg-cover bg-top">
        <button class="btn btn-sm btn-primary">Devoluciones</button>
        <button class="btn btn-sm btn-primary">Precios</button>
    </div>

    <livewire:branch.inventory-branch :branchId="$branch->id" />

</x-filament-panels::page>
