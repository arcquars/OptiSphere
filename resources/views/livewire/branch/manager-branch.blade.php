<div>
    {{-- Se eliminó h-screen y se aseguró el ancho completo con w-full --}}
    <div class="grid w-full grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Columna Izquierda: Productos y Servicios -->
        {{-- Se eliminó h-full para que la altura se ajuste al contenido --}}
        <div class="lg:col-span-2 flex flex-col gap-4">
            <!-- Header de Búsqueda y Filtros -->
            <div class="bg-base-100 p-4 rounded-box shadow-sm flex flex-col md:flex-row gap-4 items-center">

                <!-- COMPONENTE DE BÚSQUEDA TIPO SELECT SEARCHABLE -->
                <div class="dropdown w-full md:w-auto flex-grow">
                    <label class="input input-bordered flex items-center gap-2 focus-within:outline-none w-full">
                        <i class="fa-solid fa-magnifying-glass opacity-70"></i>
                        <input
                            wire:model.live.debounce.300ms="searchTerm"
                            type="text"
                            class="grow focus:outline-none"
                            placeholder="Buscar producto o servicio..."
                        />
                    </label>

                    {{-- El dropdown con los resultados --}}
                    @if(!empty($searchResults))
                        <ul tabindex="0" class="dropdown-content z-[10] menu p-2 shadow bg-base-100 rounded-box w-full mt-2">
                            @forelse($searchResults as $result)
                                <li>
                                    <a wire:click.prevent="selectAndAddToCart({{ $result['id'] }}, '{{ $result['type'] }}')">
                                        <div class="flex items-center gap-3">
                                            @if($result['type'] === 'product')
                                                <img src="{{ $result['image'] }}" class="w-8 h-8 rounded-md object-cover" />
                                            @else
                                                <span class="w-8 h-8 flex items-center justify-center"><i class="fa-solid fa-concierge-bell text-primary text-xl"></i></span>
                                            @endif
                                            <div>
                                                <div class="font-bold">{{ $result['name'] }}</div>
                                                <div class="text-sm opacity-50">${{ number_format($result['price'], 2) }}</div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li><a>No se encontraron resultados.</a></li>
                            @endforelse
                        </ul>
                    @endif
                </div>

                <select wire:model.live.debounce.300ms="selectedCategory" class="select select-bordered w-full md:w-auto focus:outline-none">
                    <option value="">Categorías</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Pestañas y Contenedor de Grids -->
            <div class="flex-grow flex flex-col">
                <div role="tablist" class="tabs tabs-lifted">
                    <a role="tab" class="tab @if($activeTab === 'products') tab-active @endif" wire:click="changeTab('products')">
                        <i class="fa-solid fa-box-archive mr-2"></i>
                        Productos
                    </a>
                    <a role="tab" class="tab @if($activeTab === 'services') tab-active @endif" wire:click="changeTab('services')">
                        <i class="fa-solid fa-concierge-bell mr-2"></i>
                        Servicios
                    </a>
                </div>
                {{-- Se eliminó min-h-[50vh] para que la altura sea flexible --}}
                <div class="flex-grow overflow-hidden bg-base-100 p-4 rounded-b-box shadow-sm">
                    <!-- Grid de Productos -->
                    <div id="product-grid" class="@if($activeTab !== 'products') hidden @endif">
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">

                            @foreach($products1 as $product)
                                <div wire:click="addToCart({{ $product->id }}, 'product', '{{ $product->name }}', {{ $product->getPriceByType($branch->id) }}, {{ $product->stockByBranch($branch->id) }})"
                                     class="card card-compact bg-base-200 shadow-md cursor-pointer hover:border-primary border-2 border-transparent transition-all duration-300 hover:scale-[1.02]">

                                    <!-- 1. Contenedor de Imagen de Altura Fija y Relación de Aspecto (1:1) -->
                                    <figure class="relative aspect-square w-full overflow-hidden p-2 bg-white">
                                        <img
                                            src="{{ $product->getUrlImage() }}"
                                            alt="{{ $product->name }}"
                                            class="object-contain w-full h-full"
                                            onerror="this.onerror=null; this.src='https://placehold.co/150x150/e5e7eb/4b5563?text=Sin+Imagen';"
                                        />
                                    </figure>

                                    <!-- 2. Contenido alineado al fondo con Flexbox -->
                                    <div class="card-body p-3 flex flex-col">

                                        <!-- Nombre del producto, limitado a dos líneas -->
                                        <h2 class="card-title text-sm line-clamp-2 leading-tight h-10 mb-1">
                                            {{ $product->name }} <small>(q: {{ $product->stockByBranch($branch->id) }})</small>
                                        </h2>

                                        <!-- Precio: 'mt-auto' lo empuja hacia el fondo de la tarjeta-body -->
                                        <p class="text-lg font-bold text-primary mt-auto">
                                            ${{ number_format($product->getPriceByType($branch->id, $saleType), 2) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-2">
                            {{ $products1->links() }}
                        </div>
                    </div>
                    <!-- Grid de Servicios -->
                    <div id="service-grid" class="@if($activeTab !== 'services') hidden @endif">
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach($services1 as $service)
                                <div wire:click="addToCart({{ $service->id }}, 'service', '{{ $service->name }}', {{ $service->getPriceByType($branch->id, $saleType) }}, {{ \App\Models\Service::QUANTITY_DEFAULT }})"
                                     class="card card-compact bg-base-200 shadow-md cursor-pointer hover:border-primary border-2 border-transparent transition-all duration-300 hover:scale-105">
                                    <figure class="px-10 pt-10">
                                        <img
                                            src="{{ $service->getUrlImage() }}"
                                            alt="{{ $service->name }}"
                                            class="object-contain w-full h-full"
                                            onerror="this.onerror=null; this.src='https://placehold.co/150x150/e5e7eb/4b5563?text=Sin+Imagen';"
                                        />
                                    </figure>
                                    <div class="card-body items-center text-center">
                                        <h2 class="card-title text-sm">{{ $service->name }}</h2>
                                        <p class="text-lg font-bold">${{ number_format($service->getPriceByType($branch->id, $saleType), 2) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-2">
                            {{ $services1->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Carrito y Pago -->
        {{-- Se eliminó h-full para que la altura se ajuste al contenido --}}
        <div class="lg:col-span-1 flex flex-col bg-base-100 rounded-box shadow-lg p-4 gap-4">
            <!-- Sección Cliente -->
            <div class="join w-full">
                <input wire:model="customerSearch" class="input input-bordered join-item w-full focus:outline-none" placeholder="Buscar cliente..."/>
                <button class="btn join-item btn-accent" onclick="document.getElementById('modal_cliente').showModal()"><i class="fa-solid fa-user-plus"></i></button>
            </div>

            <!-- Tipo de Venta -->
            <div role="tablist" class="tabs tabs-boxed tabs-sm">
                <a role="tab" class="tab @if($saleType === \App\Models\Price::TYPE_NORMAL) tab-active text-indigo-700 @endif" wire:click="$set('saleType', '{{ \App\Models\Price::TYPE_NORMAL }}')">Normal</a>
                <a role="tab" class="tab @if($saleType === \App\Models\Price::TYPE_ESPECIAL) tab-active text-indigo-700 @endif" wire:click="$set('saleType', '{{ \App\Models\Price::TYPE_ESPECIAL }}')">Cliente Especial</a>
                <a role="tab"
                   class="tab @if($saleType === \App\Models\Price::TYPE_MAYORISTA) tab-active text-indigo-700 @endif @if(!$canTypeMayor) line-through cursor-not-allowed pointer-events-none @endif"
                   wire:click="$set('saleType', '{{ \App\Models\Price::TYPE_MAYORISTA }}')"
                >
                    Por Mayor
                </a>
            </div>

            <!-- Lista de Productos en Carrito -->
            <div id="cart-items" class="flex-grow overflow-y-auto border-t border-b border-base-200 py-2 min-h-[200px]">
                <div class="space-y-2">
                    @forelse($cart as $key => $item)
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-base-200">
                            <div class="flex-grow">
                                <p class="font-bold">{{ $item['name'] }}
                                    @if($item['type'] === 'service')
                                        <span class="badge badge-info badge-xs ml-2">Servicio</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500">${{ number_format($item['price'], 2) }} x {{ $item['quantity'] }} = ${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                            </div>
                            <input type="number" value="{{ $item['quantity'] }}"
                                   wire:change="updateCartQuantity('{{ $key }}', $event.target.value)"
                                   class="input input-bordered input-sm w-16 text-center"
                                   min="1" max="{{ $item['limit'] }}"
                            />
                            <button wire:click="removeFromCart('{{ $key }}')" class="btn btn-sm btn-ghost text-error"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                    @empty
                        <p class="text-center text-gray-500">El carrito está vacío.</p>
                    @endforelse
                </div>
            </div>

            <!-- Sección de Totales y Descuento -->
            <div>
                <div class="join w-full mb-2">
                    <input type="number" placeholder="Descuento %" wire:model.lazy="discountPercentage" class="input input-bordered join-item w-full focus:outline-none"/>
                    <button wire:click="applyDiscount" class="btn join-item btn-secondary">Aplicar</button>
                </div>
                <div class="space-y-1 text-md">
                    <div class="flex justify-between"><span>Subtotal:</span> <span>${{ number_format($subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span>Descuento ({{ $discountPercentage }}%):</span> <span class="text-error">-${{ number_format($discountAmount, 2) }}</span></div>
                    <div class="flex justify-between font-bold text-xl"><span>TOTAL:</span> <span>${{ number_format($total, 2) }}</span></div>
                </div>
            </div>

            <!-- Tipo de Pago -->
            <div role="tablist" class="tabs tabs-bordered tabs-sm">
                <a role="tab" class="tab @if($paymentType === 'Efectivo') tab-active @endif" wire:click="$set('paymentType', 'Efectivo')">
                    <i class="fa-solid fa-money-bill-wave mr-2"></i>Efectivo
                </a>
                <a role="tab" class="tab @if($paymentType === 'Transferencia') tab-active @endif" wire:click="$set('paymentType', 'Transferencia')">
                    <i class="fa-solid fa-credit-card mr-2"></i>Transferencia
                </a>
                <a role="tab" class="tab @if($paymentType === 'QR') tab-active @endif" wire:click="$set('paymentType', 'QR')">
                    <i class="fa-solid fa-qrcode mr-2"></i>Pago QR
                </a>
            </div>

            <!-- Botones de Acción -->
            <div class="grid grid-cols-2 gap-2">
                <button wire:click="completePayment" class="btn btn-success btn-block">
                    <i class="fa-solid fa-check"></i>Completar Pago
                </button>
                <button class="btn btn-info btn-block">
                    <i class="fa-solid fa-print"></i>Generar Factura
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para Registrar Cliente -->
    <dialog id="modal_cliente" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Registrar Nuevo Cliente</h3>
            <form wire:submit.prevent="saveCustomer">
                <div class="py-4 space-y-4">
                    <input type="text" placeholder="Nombre completo" wire:model="newCustomerName" class="input input-bordered w-full" />
                    @error('newCustomerName') <span class="text-error text-sm">{{ $message }}</span> @enderror

                    <input type="text" placeholder="NIT / Cédula" wire:model="newCustomerNit" class="input input-bordered w-full" />
                    @error('newCustomerNit') <span class="text-error text-sm">{{ $message }}</span> @enderror

                    <input type="email" placeholder="Email (opcional)" wire:model="newCustomerEmail" class="input input-bordered w-full" />
                    @error('newCustomerEmail') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn" onclick="document.getElementById('modal_cliente').close()">Cerrar</button>
                </div>
            </form>
        </div>
    </dialog>

    @push('scripts')
        <script>
            document.addEventListener('livewire:load', function () {
                // Escucha el evento del backend para cerrar el modal
                Livewire.on('close-customer-modal', () => {
                    document.getElementById('modal_cliente').close();
                });
            });
        </script>
    @endpush
</div>

