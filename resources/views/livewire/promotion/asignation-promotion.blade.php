<div class="p-6 bg-base-100 rounded-box shadow-xl space-y-8">

    <!-- Mensajes de estado (Alertas DaisyUI) -->
    @if (session()->has('success') || session()->has('error') || session()->has('warning'))
        <div
            role="alert"
            class="alert transition duration-150 ease-in-out
            @if(session()->has('success')) alert-success
            @elseif(session()->has('error')) alert-error
            @else alert-warning @endif"
        >
            @if(session()->has('success'))
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            @elseif(session()->has('error'))
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            @endif
            <span>{{ session('success') ?? session('error') ?? session('warning') }}</span>
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">

        <!-- CARD DEL BUSCADOR -->
        <div class="card bg-white shadow-lg border border-base-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="card-body">
                <h2 class="card-title text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    Buscar Artículos a Asignar
                </h2>

                <!-- Controles de Búsqueda -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">

                    <!-- Tipo de Búsqueda (Select) -->
                    <filedset class="md:col-span-1">
                        <label class="label"><span class="label-text">Buscar por Tipo:</span></label>
                        <select
                            wire:model="searchType"
                            class="select select-sm w-full focus-within:outline-none"
                        >
                            <option value="product">Productos</option>
                            <option value="service">Servicios</option>
                            <option value="base_code">Código Base</option>
                        </select>
                    </filedset>

                    <!-- Input de Búsqueda -->
                    <filedset class="md:col-span-3">
                        <label for="search" class="label"><span class="label-text">Nombre o Código (mínimo 3 caracteres)</span></label>
                        <div class="relative">
                            <input
                                wire:model.live.debounce.300ms="searchQuery"
                                type="text"
                                id="search"
                                placeholder="Ej: Camisa Slim o CS001"
                                class="input input-sm w-full pr-10 focus-within:outline-none"
                            />
                            <div wire:loading.delay wire:target="searchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="loading loading-spinner loading-sm text-primary"></span>
                            </div>
                        </div>
                        @error('searchQuery') <div class="label-text-alt text-error">{{ $message }}</div> @enderror
                    </filedset>
                </div>

                <!-- Resultados de Búsqueda -->
                @if ($searchResults->isNotEmpty())
                    <div class="bg-base-200 p-4 rounded-lg border border-primary/50">
                        <h4 class="text-md font-semibold text-primary mb-2">
                            Resultados de {{ $searchType === 'product' ? 'Productos' : ($searchType === 'service'? 'Servicios' : 'Codigo Base') }}:
                        </h4>
                        <div class="space-y-1">
                            @if(strcmp($searchType, 'code') == 0)
                                @foreach ($searchResults as $item)
                                    <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-base-300">
                                        <p class="font-medium text-base">
                                            {{ $item->base_code }} <span class="font-normal text-sm opacity-70">({{ $item->base_code }})</span>
                                        </p>
                                        <button
                                            wire:click="addItem('{{ $item->base_code }}')"
                                            type="button"
                                            class="btn btn-success btn-xs"
                                        >
                                            Añadir Codigo Base
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                @foreach ($searchResults as $item)
                                    <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-base-300">
                                        <p class="font-medium text-base">
                                            {{ $item->name }} <span class="font-normal text-sm opacity-70">({{ $item->code }})</span>
                                        </p>
                                        <button
                                            wire:click="addItem('{{ $item->id }}')"
                                            type="button"
                                            class="btn btn-success btn-xs"
                                        >
                                            Añadir
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @elseif (strlen($searchQuery) >= 3 && $searchResults->isEmpty())
                    <div role="alert" class="alert alert-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>No se encontraron {{ $searchType === 'product' ? 'productos' : 'servicios' }} que coincidan con "{{ $searchQuery }}".</span>
                    </div>
                @endif
            </div>
        </div>
        <!-- FIN CARD DEL BUSCADOR -->

        <!-- TABLA DE ARTÍCULOS ADJUNTOS -->
        <div class="card bg-white shadow-lg border border-base-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="card-body">
                <h2 class="card-title text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Artículos Asignados (<span class="font-mono text-lg">{{ count($attachedItems) }}</span>)
                </h2>

                @if (empty($attachedItems))
                    <div class="hero rounded-box bg-base-200 border border-dashed border-base-300">
                        <div class="hero-content text-center">
                            <div class="max-w-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto opacity-60 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                <p class="text-sm opacity-80">Aún no has asignado ningún producto o servicio a esta promoción. Usa el buscador de arriba.</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-lg border border-base-300">
                        <table class="table table-zebra w-full">
                            <thead>
                            <tr>
                                <th class="w-1/4">Tipo</th>
                                <th class="w-1/4">Código</th>
                                <th class="w-1/2">Nombre</th>
                                <th class="text-right">Acción</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($attachedItems as $item)
                                <tr wire:key="item-{{ $item['key'] }}">
                                    <td>
                                        @switch($item['type'])
                                            @case('service')
                                                <div class="badge badge-secondary badge-outline">
                                                    Servicio
                                                </div>
                                                @break
                                            @case('base_code')
                                                <div class="badge badge-info badge-outline">
                                                    Codigo base
                                                </div>
                                                @break
                                            @default
                                                <div class="badge badge-primary badge-outline">
                                                    Producto
                                                </div>
                                                
                                        @endswitch
                                    </td>
                                    <td><span class="font-mono">{{ $item['code'] }}</span></td>
                                    <td>{{ $item['name'] }}</td>
                                    <td class="text-right">
                                        @if(strcmp($item['type'], 'base_code') != 0)
                                        <button
                                            wire:click="removeItem('{{ $item['key'] }}')"
                                            type="button"
                                            class="btn btn-ghost btn-xs text-error"
                                        >
                                            <i class="fa-regular fa-trash-can"></i>
                                            Eliminar
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        <!-- FIN TABLA -->

        <!-- Botón de Guardar Fijo (Sticky Footer) -->
        <div class="sticky bottom-0 p-4 bg-base-100/90 backdrop-blur-sm rounded-xl shadow-2xl flex justify-end z-10">
            <button
                type="submit"
                class="btn btn-primary btn-lg shadow-lg"
                wire:loading.attr="disabled"
                @if (count($attachedItems) === 0) disabled @endif
            >
                <svg wire:loading wire:target="save" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="save">
                    Guardar Asignación ({{ count($attachedItems) }})
                </span>
                <span wire:loading wire:target="save">Guardando cambios...</span>
            </button>
        </div>
    </form>
</div>
