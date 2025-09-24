<div
    x-data='{
    searchByCode: false
    }'
>
    <button wire:click="toggleForm" class="btn btn-sm btn-primary">Ingresar productos</button>

    @if (session('success'))
        <div role="alert" class="alert alert-success mt-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($showForm)
        <div class="modal modal-open">
            <div class="modal-box w-11/12 max-w-5xl">
                <h3 class="font-bold text-lg mb-4">Ingresar productos al inventario</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <div x-show="!searchByCode" class="mb-4">
                            <form wire:submit="searchProducts">
                                <div class="join">
                                    <label class="input input-sm input-bordered flex items-center gap-2 join-item focus-within:outline-none">
                                        <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                            <g
                                                stroke-linejoin="round"
                                                stroke-linecap="round"
                                                stroke-width="2.5"
                                                fill="none"
                                                stroke="currentColor"
                                            >
                                                <circle cx="11" cy="11" r="8"></circle>
                                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                            </g>
                                        </svg>
                                        <input type="text" placeholder="Nombre/Codigo" wire:model.defer="searchQuery"/>
                                    </label>
                                    <button type="submit" class="btn btn-sm btn-neutral join-item">Buscar</button>
                                </div>
                            </form>
                        </div>
                        <div x-show="searchByCode" class="mb-4">
                            <label class="input input-sm focus-within:outline-none">
                                <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <g
                                        stroke-linejoin="round"
                                        stroke-linecap="round"
                                        stroke-width="2.5"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.3-4.3"></path>
                                    </g>
                                </svg>
                                <input type="text" wire:model.defer="searchQuery" wire:keydown.enter="searchCode"
                                       placeholder="Buscar codigo" class=""/>
                            </label>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <label for="fs-code" class="label cursor-pointer">
                                <span class="label-text">Codigo</span>
                            </label>
                            <input type="checkbox" id="fs-code" x-model:searchByCode="searchByCode" class="toggle toggle-info"/>
                        </div>
                    </div>
                </div>
                @if ($searchResults->count() > 0)
                    <ul class="menu bg-base-200 w-full rounded-box mb-4">
                        @foreach($searchResults as $product)
                            <li>
                                <a wire:click.prevent="$dispatch('productSelected', { productId: {{ $product->id }} })">{{ $product->name }}
                                    ({{ $product->code }})</a></li>
                        @endforeach
                    </ul>
                    {{ $searchResults->links() }}
                @endif

                <div class="overflow-x-auto mb-4">
                    <table class="table table-zebra w-full">
                        <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Acción</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($selectedProducts as $product)
                            <tr wire:key="{{ $product->id }}">
                                <td>{{ $product->name }} <small>({{ $product->code }})</small></td>
                                <td>
                                    <input type="number" class="input input-sm input-bordered w-20 focus-within:outline-none"
                                           wire:model.live="productQuantities.{{ $product->id }}" min="1">
                                </td>
                                <td class="text-right">
                                    <button wire:click="removeProduct({{ $product->id }})" class="btn btn-error btn-sm">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="modal-action">
                    <button wire:click="saveEntry" @if(count($selectedProducts) <= 0) disabled
                            @endif class="btn btn-sm btn-success">Guardar
                    </button>
                    <button wire:click="toggleForm" class="btn btn-sm">Cancelar</button>
                </div>
            </div>
        </div>
    @endif
</div>
