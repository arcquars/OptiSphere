<x-filament-panels::page>
    <h1>{{ $branch->name }}</h1>

    <livewire:cash-box-closing.list-cbc :branchId="$branch->id" />
</x-filament-panels::page>
