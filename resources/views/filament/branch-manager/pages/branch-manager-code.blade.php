<x-filament-panels::page>
{{--    {{ $branch->name }}--}}
    @if($branch->is_facturable)
    <livewire:sale.open-siat-event-modal :branch-id="$branch->id" />
    @endif
    <livewire:branch.manager-branch-code :branch-id="$branch->id" />
        
    <livewire:branch.history-product-modal :branch-id="$branch->id" />
</x-filament-panels::page>
