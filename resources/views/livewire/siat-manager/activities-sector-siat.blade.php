<div>
    <div class="grid grid-cols-2 gap-1">
        <div>
            <h2 class="text-xl text-primary font-bold">Actividades Documento Sector</h2>
        </div>
        <div class="text-right">
            <button 
                class="btn btn-s, btn-primary"
                wire:click="getItems"
                wire:loading.attr="disabled"
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
        <table class="table table-zebra">
            <!-- head -->
            <thead>
                <tr>
                    <th>Nro</th>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                @if($items)
                @foreach ($items as $index => $item)
                <tr>
                    <th>{{ $index }}</th>
                    <td>{{ $item->codigo_actividad }}</td>
                    <td>{{ $item->codigo_documento_sector }}</td>
                    <td>{{ $item->tipo_documento_sector }}</td>
                </tr>    
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
