<x-filament-panels::page>
{{--    {{ $branch->name }}--}}
    <livewire:sale.open-siat-event-modal :branch-id="$branch->id" />
    <livewire:branch.history-product-modal :branch-id="$branch->id" />
    <livewire:branch.manager-branch-code :branch-id="$branch->id" />
</x-filament-panels::page>
