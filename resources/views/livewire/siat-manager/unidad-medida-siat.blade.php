<div>
    <div class="grid grid-cols-2 gap-1">
        <div>
            <h2 class="text-xl text-primary font-bold">Unidad de Medida</h2>
        </div>
        <div class="text-right">
            <button 
                class="btn btn-sm btn-primary"
                wire:click="getItems"
                wire:loading.attr="disabled"
                wire:target="getItems"
            >
                Sincronizar
            </button>
        </div>
    </div>
    <span wire:loading wire:target="getItems" class="text-warning text-lg">
        <span class="loading loading-spinner loading-xs mr-2"></span>
        Sincronizando...
    </span>
    <div class="overflow-x-auto">
        @include('partials.siat-code-list', ['items' => $items])
    </div>
</div>