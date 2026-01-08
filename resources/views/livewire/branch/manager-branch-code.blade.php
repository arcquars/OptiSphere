<div>
    <div class="grid w-full grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Columna Izquierda: Productos y Servicios -->
        <div class="lg:col-span-1 flex flex-col gap-1">
            <!-- Header de Búsqueda y Filtros -->
            <div class="bg-base-100 p-1 rounded-box shadow-sm join w-full flex">
                <div class="w-3/4">
                    <label class="input join-item flex items-center gap-2 w-full focus-within:outline-none">
                        <i class="fa-solid fa-magnifying-glass opacity-70"></i>
                        <input
                            wire:model.live.debounce.800ms="searchTerm"
                            wire:keydown.enter.prevent="scanCode($el.value); $el.value = ''"
                            type="text"
                            placeholder="Buscar producto o escanear código..."
                            autofocus
                            class="grow focus:outline-none"
                        />
                    </label>
                </div>
                <select
                    wire:model.live.debounce.300ms="selectedCategory" 
                    class="select select-bordered join-item w-1/4 focus:outline-none"
                >
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
                        <div class="overflow-x-auto">
                            <table class="table table-zebra w-full">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock</th>
                                        <th class="text-right">Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products1 as $product)
                                        <tr 
                                            class="cursor-pointer hover:bg-orange-300 transition-colors"
                                            wire:click="addToCart({{ $product->id }}, 'product', '{{ $product->name }}', {{ $product->getPriceByType($branch->id, $saleType) }}, {{ $product->stockByBranch($branch->id) }})"
                                            wire:key="prod-row-{{ $product->id }}"
                                        >
                                            <td class="font-bold w-3/5">
                                                {{ $product->name }} <small>({{ $product->code }})</small>
                                            </td>
                                            <td class="w-1/5">
                                                <span class="badge badge-sm {{ $product->stockByBranch($branch->id) > 0 ? 'badge-ghost' : 'badge-error' }}">
                                                    {{ $product->stockByBranch($branch->id) }}
                                                </span>
                                            </td>
                                            <td class="text-right font-mono font-bold w-1/5">
                                                ${{ number_format($product->getPriceByType($branch->id, $saleType), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
        <div class="lg:col-span-2 flex flex-col bg-base-100 rounded-box shadow-lg p-4 gap-4">
            <!-- Sección Cliente -->
            <livewire:customer.search-customer />
            <livewire:customer.create-customer />

            <form wire:submit.prevent="">
                <h1 class="text-xl font-semibold  {{ $customer? "text-indigo-700" : "text-red-700" }}">
                    {{ $customer? $customer->name . "(" . $customer->document_type_show . ")" : "Sin Cliente seleccionado" }}
                </h1>
                @if($this->eventSiatDto != null)
                <div class="grid grid-cols-2 gap-2">
                    <fieldset class="fieldset">
                        <label class="label">Nro. factura</label>
                        <input wire:model.defer="invoice_nro" type="number" class="input input-sm w-full">
                    </fieldset>
                    <fieldset class="fieldset">
                        <label class="label">Fecha / Hora</label>
                        <input wire:model.defer="invoice_date" type="datetime-local" class="input input-sm w-full">
                    </fieldset>
                </div>
                @endif
                <!-- Tipo de Venta -->
                <div role="tablist" class="tabs tabs-boxed tabs-sm">
                    <a role="tab" class="tab @if($saleType === \App\Models\Price::TYPE_NORMAL) tab-active text-indigo-700 @endif" wire:click="$set('saleType', '{{ \App\Models\Price::TYPE_NORMAL }}')">Normal</a>
                    <a role="tab" class="tab @if($saleType === \App\Models\Price::TYPE_ESPECIAL) tab-active text-indigo-700 @endif" wire:click="$set('saleType', '{{ \App\Models\Price::TYPE_ESPECIAL }}')">Cliente Especial</a>
                    <a role="tab"
                       class="
                        tab
                        @if($saleType === \App\Models\Price::TYPE_MAYORISTA) tab-active text-indigo-700 @endif
                        @if(!$canTypeMayor) line-through cursor-not-allowed pointer-events-none @endif"
                       wire:click="$set('saleType', '{{ \App\Models\Price::TYPE_MAYORISTA }}')"
                    >
                        Por Mayor
                    </a>
                </div>

                <!-- Lista de Productos en Carrito -->
                <div id="cart-items" class="flex-grow overflow-y-auto border-t border-b border-neutral-200 py-1 min-h-[200px]">
                    <div class="space-y-2">
                        @forelse($cart as $key => $item)
                            <div class="gap-2 p-2 rounded-lg bg-neutral-100">
                            <div class="flex items-center">
                                <div class="flex-grow">
                                    <p class="font-bold">{{ $item['name'] }}
                                        @if($item['type'] === 'service')
                                            <span class="badge badge-info badge-xs ml-2">Servicio</span>
                                        @endif
                                        @if($item['promotion'])
                                        <span class="badge badge-info badge-xs ml-2">
                                            {{ ($item['promotion'])? "-" .$item['promotion'] . "%" : "" }}
                                        </span>
                                        @endif
                                        <span class="text-sm text-gray-500">
                                            @if($item['promotion'] == null)
                                                {{ config('cerisier.currency_symbol') }}{{ number_format($item['price'], 2) }} x {{ $item['quantity'] }} = {{ config('cerisier.currency_symbol') }}{{ number_format($item['price'] * $item['quantity'], 2) }}
                                            @else
                                                {{ config('cerisier.currency_symbol') }}({{ number_format($item['price'], 2) }} - {{ number_format(($item['price'] *  ($item['promotion']/100)), 2) }}) x {{ $item['quantity'] }} = {{ config('cerisier.currency_symbol') }}{{ number_format(($item['price'] - $item['price'] *  ($item['promotion']/100) )* $item['quantity'], 2) }}
                                            @endif
                                        </span>
                                    </p>
                                    {{-- <p class="text-sm text-gray-500">
                                        @if($item['promotion'] == null)
                                            {{ config('cerisier.currency_symbol') }}{{ number_format($item['price'], 2) }} x {{ $item['quantity'] }} = {{ config('cerisier.currency_symbol') }}{{ number_format($item['price'] * $item['quantity'], 2) }}
                                        @else
                                            {{ config('cerisier.currency_symbol') }}({{ number_format($item['price'], 2) }} - {{ number_format(($item['price'] *  ($item['promotion']/100)), 2) }}) x {{ $item['quantity'] }} = {{ config('cerisier.currency_symbol') }}{{ number_format(($item['price'] - $item['price'] *  ($item['promotion']/100) )* $item['quantity'], 2) }}
                                        @endif
                                    </p> --}}
                                </div>
                                <button
                                    type="button"    
                                    wire:click="incrementCartQuantity('{{ $key }}',false)"  
                                    class="btn btn-square btn-sm btn-outline btn-accent">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <p class="badge badge-lg mx-1">{{ $item['quantity'] }}</p>
                                <button
                                    type="button"
                                    wire:click="incrementCartQuantity('{{ $key }}', true)" 
                                    class="btn btn-square btn-sm btn-outline btn-accent">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                                @if($item['type'] !== 'service')
                                    <div class="dropdown dropdown-left mx-1 drodown">
                                        <div 
                                            tabindex="0" 
                                            role="button" 
                                            class="btn btn-sm btn-outline btn-accent"
                                        >
                                            <i class="fa-solid fa-cart-plus"></i>
                                        </div>
                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm">
                                            @foreach($services as $service)
                                                <li><a wire:click.stop.prevent="addServiceToProduct('{{ $key }}', {{$item['id']}}, {{ $service->id }}); document.activeElement.blur()">{{ $service->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <button wire:click.stop.prevent="removeFromCart('{{ $key }}')" 
                                    class="btn btn-sm btn-outline btn-error" title="Quitar"
                                >
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>


                            @if(isset($item['services']))
                                <hr class="my-1">
                                @foreach($item['services'] as $subKey => $sub)
                                        <div class="pl-4 flex items-center pb-1">
                                            <div class="flex-grow">
                                                <p class="font-bold text-sm">
                                                    <i class="fa-solid fa-check"></i>
                                                    {{ $sub['name'] }}
                                                    <span class="badge badge-info badge-xs ml-2">Servicio</span>
                                                    @if($sub['promotion'])
                                                        <span class="badge badge-info badge-xs ml-2">
                                                            {{ ($sub['promotion'])? "-" .$sub['promotion'] . "%" : "" }}
                                                        </span>
                                                    @endif
                                                    <span class="text-sm text-base text-gray-500 pl-2">
                                                        {{ $sub['promotion'] }}
                                                        @if($sub['promotion'] == null)
                                                            {{ config('cerisier.currency_symbol') }}{{ number_format($sub['price'], 2) }} x {{ $sub['quantity'] }} = {{ config('cerisier.currency_symbol') }}{{ number_format($sub['price'] * $sub['quantity'], 2) }}
                                                        @else
                                                            {{ config('cerisier.currency_symbol') }}({{ number_format($sub['price'], 2) }} - {{ number_format(($sub['price'] *  ($sub['promotion']/100)), 2) }}) x {{ $sub['quantity'] }} = {{ config('cerisier.currency_symbol') }}{{ number_format(($sub['price'] - $sub['price'] *  ($sub['promotion']/100) )* $sub['quantity'], 2) }}
                                                        @endif
                                                    </span>
                                                </p>
                                                
                                            </div>
                                            <button 
                                                type="button" 
                                                wire:click.stop.prevent="removeSubFromCart('{{ $key }}', '{{ $subKey }}')" 
                                                class="btn btn-xs btn-outline btn-error" title="Quitar"
                                            >
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                @endforeach
                            @endif
                    </div>
                        @empty
                            <p class="text-center text-gray-500">El carrito está vacío.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card mt-2 border p-1">
                    <div class="flex items-center justify-between m-2">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fa-solid fa-tags text-pink-500"></i> Promociones
                        </h3>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox"
                                   class="toggle toggle-accent focus-within:outline-none"
                                   wire:model.live="promoActive" />
                        </label>
                    </div>

                    {{-- Mostrar opciones solo si está activa la promo --}}
                    @if($promoActive)
                        <div class="mt-3 space-y-2">
                            <select wire:model.live="selectedPromo" class="select select-bordered w-full focus-within:outline-none">
                                <option value="">Seleccionar promoción</option>
                            @foreach($promotionActives as $promo)
                                <option value="{{ $promo->id }}">
                                    {{ $promo->name }} - {{ $promo->discount_percentage }}%
                                </option>
                            @endforeach
                            </select>

                            {{-- Mostrar detalle de la promo aplicada --}}
                            @if($selectedPromo)
                                <div class="bg-pink-50 border border-pink-200 text-pink-600 rounded-lg p-3 text-sm">
                                    🎉 Promo aplicada:
                                    <span class="font-semibold">{{ $promotion->name }}</span>
                                    (-{{ $promotion->discount_percentage }}%)
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Sección de Totales y Descuento -->
                <div class="mt-1">
                    <div class="join w-full mb-2">
                        <input 
                            type="number" 
                            placeholder="Descuento %" 
                            wire:model.lazy="discountPercentage" 
                            class="input input-bordered join-item w-full focus:outline-none"
                            min="0" max="90"
                        />
                        <button wire:click.stop.prevent="applyDiscount" class="btn join-item btn-secondary">Aplicar Desc %</button>
                    </div>
                    <div class="text-error">@error('discountPercentage') {{ $message }} @enderror</div>
                    <div class="space-y-1 text-md">
                        <div class="flex justify-between"><span>Subtotal:</span> <span>${{ number_format($subtotal, 2) }}</span></div>
                        <div class="flex justify-between"><span>Descuento ({{ $discountPercentage }}%):</span> <span class="text-error">-${{ number_format($discountAmount, 2) }}</span></div>
                        @if(!$this->branch->is_facturable)
                        <div class="flex justify-between font-bold text-xl">
                            <span>TOTAL:</span> <span>${{ number_format($total, 2) }}</span>
                        </div>



                        @else
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total base Crédito Fiscal:</span> <span>${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-lg">
                            <span>Crédito Fiscal:</span> <span>${{ number_format(($total*0.13), 2) }}</span>
                        </div>
                        @endif


                        @if ($isSaleCredit)
                            <div class="mt-4">
                                <label class="font-semibold">Pago parcial:</label>
                                <input type="number" wire:model="partial_payment" min="0" max="{{ $total }}" class="input input-bordered w-full mt-2 focus:outline-none">
                                <div class="text-error">@error('partial_payment') {{ $message }} @enderror</div>
                                <p class="text-sm text-gray-500 mt-1">
                                    Restante a crédito: <strong>{{ config('cerisier.currency_symbol') }} {{ $total - (is_numeric($partial_payment))? $partial_payment: 0 }}</strong>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tipo de Pago -->
                <div role="tablist" class="tabs tabs-bordered tabs-sm">
                    <a role="tab" class="tab @if($paymentType === \App\Models\SalePayment::METHOD_CASH) tab-active @endif" wire:click="$set('paymentType', '{{ \App\Models\SalePayment::METHOD_CASH }}')">
                        <i class="fa-solid fa-money-bill-wave mr-2"></i>{{ \App\Models\SalePayment::METHOD_CASH }}
                    </a>
                    <a role="tab" class="tab @if($paymentType === \App\Models\SalePayment::METHOD_TRANSFER) tab-active @endif" wire:click="$set('paymentType', '{{ \App\Models\SalePayment::METHOD_TRANSFER }}')">
                        <i class="fa-solid fa-credit-card mr-2"></i>{{ \App\Models\SalePayment::METHOD_TRANSFER }}
                    </a>
                    <a 
                        role="tab" 
                        class="tab @if($paymentType === \App\Models\SalePayment::METHOD_QR) tab-active @endif" 
                        wire:click="$set('paymentType', '{{ \App\Models\SalePayment::METHOD_QR }}')"
                        @if($branch->configuracionBanco && $branch->configuracionBanco->activo) data-test="123" @else disabled @endif
                    >
                        <i class="fa-solid fa-qrcode mr-2"></i>Pago {{ \App\Models\SalePayment::METHOD_QR }}
                    </a>
                    @if(isset($customer) && $customer->can_buy_on_credit)
{{--                    <a role="tab" class="tab @if($paymentType === 'credito') tab-active @endif" wire:click="$set('paymentType', 'credito')">--}}
{{--                        <i class="fa-solid fa-file-half-dashed mr-2"></i>Credito--}}
{{--                    </a>--}}
                        <label class="label text-primary ml-2">
                            <input type="checkbox" wire:model.live="isSaleCredit" class="checkbox checkbox-primary" />
                            Credito
                        </label>
                    @endif
                </div>

                @if($message_error)
                <div>
                    <p class="text-base text-danger-500">{{ $message_error }}</p>
                </div>
                @endif

                <!-- Botones de Acción -->
                <div class="grid grid-cols-2 gap-2">
                    <button 
                        type="button" 
                        wire:click="completePayment1(false)" 
                        class="btn btn-success btn-block"
                        wire:loading.target="completePayment1"
                        wire:loading.attr="disabled"
                         @if(!$isOpenCashBoxClosing) disabled @endif>
                        <i class="fa-solid fa-check"></i>Completar Pago
                    </button>
                    <button
                        type="button"
                        wire:click="completePayment1(true)" 
                        class="btn btn-info btn-block text-white" 
                        wire:loading.attr="disabled"
                        @if(!$isOpenCashBoxClosing || !$this->branch->is_facturable) disabled @endif
                    >
                        <i class="fa-solid fa-file-invoice"></i> Facturar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE PAGO QR -->
    @if($showQrModal)
    <div class="modal modal-open bg-black/50 backdrop-blur-sm z-50">
        <div class="modal-box w-11/12 max-w-md text-center shadow-2xl relative">
            
            <!-- Botón cerrar (X) -->
            <button wire:click="closeQrModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            
            <h3 class="font-bold text-2xl text-primary mb-2">Pago con QR</h3>
            <p class="text-gray-500 text-sm mb-4">Escanea el código desde tu aplicación bancaria</p>
            
            <!-- Contenedor del QR -->
            <div class="flex flex-col items-center justify-center p-4 bg-white rounded-xl border border-gray-200 shadow-inner mb-4">
                @if($qrImage)
                    <img src="data:image/png;base64,{{ $qrImage }}" alt="Código QR" class="w-64 h-64 object-contain">
                    <p class="mt-2 font-mono text-xs text-gray-400">Generado por Banco Económico</p>
                @else
                    <div class="w-64 h-64 flex flex-col items-center justify-center text-gray-400">
                        <span class="loading loading-spinner loading-lg"></span>
                        <span class="mt-2 text-xs">Generando QR...</span>
                    </div>
                @endif
            </div>

            <livewire:branch.qr-payment-processor 
                :qrId="$qrId"
                {{-- wire:key="qr-modal-{{ $qrTransactionId }}" --}}
            />
            <!-- Monto -->
            <div class="stat p-0 mb-6">
                <div class="stat-title">Monto a Pagar</div>
                <div class="stat-value text-primary">
                    @if($this->isSaleCredit)
                        {{ number_format($partial_payment ?? 0, 2) }} BOB
                    @else
                        {{ number_format($total, 2) }} BOB
                    @endif
                </div>
            </div>
            @if(!empty($qrModalMessage))
            <div class="stat p-0 mb-1">
                <div class="stat-value text-error">{{ $qrModalMessage }}</div>
            </div>
            @endif
            <!-- Acciones del Modal -->
            <div class="grid grid-cols-1 gap-3">
                <!-- Botón de Confirmación Manual -->
                <button wire:click="verifyQrPayment" class="btn btn-primary w-full">
                    <i class="fa-solid fa-glasses"></i> Verificar pago
                </button>
                
                <!-- Botón Cancelar -->
                <button wire:click="closeQrModal" class="btn btn-outline w-full text-gray-500">
                    Cancelar Operación
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL DE PAGO QR -->
    @if($showNitModal)
    <div class="modal modal-open bg-black/50 backdrop-blur-sm z-50">
        <div class="modal-box w-11/12 max-w-md text-center shadow-2xl relative">
            
            <!-- Botón cerrar (X) -->
            <button wire:click="closeNitValidarModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            
            <h3 class="font-bold text-2xl text-primary mb-2">Saltar Validacion del NIT</h3>
            <p class="text-gray-500 text-sm mb-4">Saltara la validacion del nit en SIAT</p>
            
            
            
            <!-- Acciones del Modal -->
            <div class="grid grid-cols-1 gap-3">
                <!-- Botón de Confirmación Manual -->
                <button 
                    wire:click="skipValdiateNit" 
                    wire:loading.target="skipValdiateNit"
                    wire:loading.attr="disabled"
                    class="btn btn-primary w-full">
                    <i class="fa-solid fa-glasses"></i> Saltar validacion
                </button>
                
                <!-- Botón Cancelar -->
                <button 
                    wire:click="closeNitValidarModal" 
                    wire:loading.target="skipValdiateNit"
                    wire:loading.attr="disabled"
                    class="btn btn-outline w-full text-gray-500">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
<script>
    window.addEventListener('open-pdf', (event) => {
        window.open(event.detail.url, '_blank');
    });
</script>
