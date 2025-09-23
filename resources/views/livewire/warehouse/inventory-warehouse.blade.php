<div>
    <form wire:submit.prevent="search">
        <div class="join">
            <div>
                <label class="input validator join-item">
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
                    <input type="text" placeholder="Nombre/Codigo" wire:model.defer="querySearch" />
                </label>
            </div>
            <button class="btn btn-neutral join-item">Buscar</button>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="table table-zebra">
            <!-- head -->
            <thead>
            <tr>
                <th></th>
                <th>Nombre</th>
                <th>Codigo</th>
                <th>Cantidad</th>
{{--                <th>Acciones</th>--}}
            </tr>
            </thead>
            <tbody>
            @foreach ($products as $index => $product)
                <tr>
                    <th>{{ $products->firstItem() + $loop->index }}</th>
                    <td>{{ $product->id }} {{ $product->name }}</td>
                    <td>{{ $product->code }}</td>
                    <td class="text-right">{{ $product->stockByStockWarehouse(1)? $product->stockByStockWarehouse($warehouseId)->quantity : 0 }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
</div>
