<div>
    <h2 class="text-2xl text-amber-600 font-bold">{{ $branch->name }}</h2>

    <form wire:submit.prevent="search">
        <div class="join">
            <div>
                <label class="input validator join-item focus-within:outline-none">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Nombre/Codigo" wire:model.defer="querySearch" />
                </label>
            </div>
            <button type="submit" class="btn btn-neutral join-item">Buscar producto</button>
        </div>
    </form>

    <div class="overflow-x-auto mt-2">
        <table class="table table-zebra table-sm">
            <!-- head -->
            <thead class="bg-amber-600 text-white">
            <tr>
                <th></th>
                <th class="text-center">Nombre</th>
                <th class="text-center">Codigo</th>
                <th class="text-center">Cantidad</th>
                <th class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($products as $index => $product)
                @php
                $stock = $product->stocks->first();
                @endphp
                <tr>
                    <th>{{ $products->firstItem() + $loop->index }}</th>
                    <td>{{ $product->id }} {{ $product->name }}</td>
                    <td>{{ $product->code }}</td>
                    <td class="text-right">{{ $stock? $stock->quantity : 0 }}</td>
                    <td class="text-right">
                        <a href="#" class="btn btn-link no-underline" title="Editar Precios" wire:click.prevent="toggleFormPrice({{$product->id}})">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
</div>
