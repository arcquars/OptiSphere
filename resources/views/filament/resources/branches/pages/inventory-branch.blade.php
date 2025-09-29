<x-filament-panels::page>

    <div class="preview bg-base-100 relative flex flex-wrap items-start gap-2 overflow-x-hidden bg-cover bg-top">
        <livewire:branch.refund-branch :branchId="$branch->id" />
    </div>

    <livewire:branch.price-branch :branchId="$branch->id" />
    <livewire:branch.inventory-branch :branchId="$branch->id" />

</x-filament-panels::page>
