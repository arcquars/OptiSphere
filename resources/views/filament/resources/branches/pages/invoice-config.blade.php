<x-filament-panels::page>
    <h1>{{ $branch->name }}</h1>

    <div class="filament-forms-card p-6 bg-white rounded-xl shadow">
        
        <form wire:submit="submit">
            
            {{ $this->form }}
            
            <div class="mt-6">
                <x-filament::button type="submit" size="sm" color="info">
                    Guardar Configuración
                </x-filament::button>
                <x-filament::button type="button" size="sm" color="success" wire:click="validateSiat">
                    Validar
                </x-filament::button>
                <x-filament::button type="button" size="sm" color="success" wire:click="crearPuntoVenta">
                    Crear Punto de Venta
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
