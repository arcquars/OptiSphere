<div>
    <div class="grid grid-cols-2 gap-1">
        <div>
            <h2 class="text-xl text-primary font-bold">Tipos de documentos de identidad</h2>
        </div>
        <div class="text-right">
            <button 
                class="btn btn-s, btn-primary"
                wire:click="getItems"
                wire:loading.attr="disabled"  {{-- Deshabilita el botón mientras carga --}}
                wire:target="getItems"
            >
                Sincronizar
            </button>
        </div>
    </div>

    {{-- 2. Spinner y texto de carga (se muestra cuando SÍ está cargando) --}}
    <span wire:loading wire:target="getItems" class="text-warning text-lg">
        <span class="loading loading-spinner loading-xs mr-2"></span>
        Sincronizando...
    </span>
    <div class="overflow-x-auto">
        @include('partials.siat-code-list', ['items' => $items])
    </div>
</div>
