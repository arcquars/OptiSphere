<x-filament-panels::page>
    <div class="grid grid-cols-2 gap-2">
        <div>
            <p class="text-lg"><b>Almacen:</b> {{ $warehouse->name }}</p>
        </div>
        <div>
            <p class="text-lg"><b>Direccion:</b> {{ $warehouse->location }}</p>
        </div>
    </div>

    <livewire:warehouse :warehouse-id="$warehouse->id" />

</x-filament-panels::page>

