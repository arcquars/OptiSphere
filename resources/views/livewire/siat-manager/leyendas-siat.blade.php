<div>
    <div class="grid grid-cols-2 gap-1">
        <div>
            <h2 class="text-xl text-primary font-bold">Leyendas Facturas</h2>
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
                @foreach ($items as $index => $act)
                <tr>
                    <th>{{ $index + 1 }}</th>
                    <td>{{ $act->codigo_actividad }}</td>
                    <td>{{ $act->descripcion_leyenda }}</td>
                    <td>{{ $act->tipo }}</td>
                </tr>    
                @endforeach
            </tbody>
        </table>
    </div>
</div>
