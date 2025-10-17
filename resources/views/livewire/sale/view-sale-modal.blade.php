<div>
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
                    <h3 class="font-bold text-lg mb-4">Venta <span class="font-medium"># {{ $sale->id }}</span> <span class="font-normal">|</span> Sucursal <span class="font-medium">{{ $sale->branch->name }}</span></h3>

                    <div class="space-y-6">

                        <!-- Sección de Información General -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-user text-primary"></i>
                                <div>
                                    <span class="font-semibold">Cliente:</span>
                                    <span>{{ $sale->customer->name }} (NIT: {{ $sale->customer->nit }})</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-star text-primary"></i>
                                <div>
                                    <span class="font-semibold">Tipo de Venta:</span>
                                    <span>Cliente {{ strtoupper($sale->sale_type) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Items Vendidos -->
                        <div>
                            <h4 class="font-semibold mb-2">Items en el Carrito</h4>
                            <div class="overflow-x-auto">
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-right">P. Unit.</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($sale->items as $item)
                                        <tr>
                                            <td>
                                                {{ $item->salable->name ?? '—' }}

                                                @if ($item->is_service)
                                                    <div class="badge badge-info badge-xs ml-1">{{ $item->type_label }}</div>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-right font-mono">
                                                @if($item->promotion_id)
                                                {{ $item->final_price_per_unit }} <small>({{ $item->base_price }} - {{ $item->promotion_discount_rate }}%)</small>
                                                @else
                                                {{ $item->final_price_per_unit }}
                                                @endif
                                            </td>
                                            <td class="text-right font-mono">
                                                {{ config('cerisier.currency_symbol') }} {{ $item->subtotal }}
                                            </td>
                                        </tr>
                                        @if(count($item->attachedServices) > 0)
                                            @foreach($item->attachedServices as $attached)
                                                <tr>
                                                    <td class="pl-6">
                                                        {{ $attached->service->name ?? '—' }}
                                                        <div class="badge badge-info badge-xs ml-1">Servicio</div>
                                                    </td>
                                                    <td class="text-center">{{ $attached->quantity }}</td>
                                                    <td class="text-right font-mono">
                                                        @if($attached->promotion_id)
                                                            {{ number_format($attached->price_per_unit - ($attached->price_per_unit * $attached->promotion_discount_rate / 100), 2) }} <small>({{ $attached->price_per_unit }} - {{ $attached->promotion_discount_rate }}%)</small>
                                                        @else
                                                            {{ $attached->price_per_unit }}
                                                        @endif
                                                    </td>
                                                    <td class="text-right font-mono">
                                                        {{ config('cerisier.currency_symbol') }} {{ $attached->subtotal }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Sección de Totales y Pago -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-base-200 p-4 rounded-box">
                            <!-- Columna de Promoción y Método de Pago -->
                            <div class="space-y-4">
                                @if($sale->use_promotion)
                                <div>
                                    <h4 class="font-semibold mb-2">Promoción Aplicada</h4>
                                    <div class="alert alert-success p-2">
                                        <i class="fa-solid fa-ticket"></i>
                                        <span><strong>{{ $promotion->name }}</strong></span>
                                    </div>
                                </div>
                                @endif
                                <div>
                                    <h4 class="font-semibold mb-2">Método de Pago</h4>
                                    <div class="flex items-center gap-3">
                                        @if($sale->status == \App\Models\Sale::SALE_STATUS_PAID)
                                        <i class="fa-solid fa-file-invoice-dollar text-primary fa-lg"></i>
                                        <span class="font-semibold text-primary">{{ __('cerisier.' . $sale->status) }}</span>
                                        @else
                                            <i class="fa-solid fa-file-invoice-dollar text-warning fa-lg"></i>
                                            <span class="font-semibold text-warning">{{ __('cerisier.' . $sale->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                                    <div>
                                        <h4 class="font-semibold mb-2">Tipo de pago</h4>
                                        <div class="flex items-center gap-3">
                                            @switch($sale->payment_method)
                                                @case(\App\Models\SalePayment::METHOD_TRANSFER)
                                                <div class="badge badge-outline badge-secondary">
                                                    <i class="fa-solid fa-credit-card mr-1"></i>{{ $sale->payment_method }}
                                                </div>
                                                @break
                                                @case(\App\Models\SalePayment::METHOD_QR)
                                                <div class="badge badge-outline badge-info">
                                                    <i class="fa-solid fa-qrcode mr-1"></i>{{ $sale->payment_method }}
                                                </div>
                                                @break
                                                @default
                                                <div class="badge badge-outline badge-success">
                                                    <i class="fa-solid fa-money-bill-wave mr-1"></i>{{ $sale->payment_method }}
                                                </div>
                                            @endswitch
                                        </div>
                                    </div>
                            </div>

                            <!-- Columna de Resumen Financiero -->
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-base-content/70">Subtotal:</span>
                                    <span class="font-mono">{{ config('cerisier.currency_symbol') }} {{ $sale->total_amount }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-base-content/70">Descuento:</span>
                                    <span class="font-mono text-error">-{{ $sale->final_discount }}%</span>
                                </div>
                                <div class="divider my-1"></div>
                                <div class="flex justify-between font-bold text-lg">
                                    <span>Total a Pagar:</span>
                                    <span class="font-mono">{{ config('cerisier.currency_symbol') }} {{ $sale->final_total }}</span>
                                </div>
                                @if($sale->status == \App\Models\Sale::SALE_STATUS_CREDIT)
                                    <div class="flex justify-between text-info">
                                        <span>A Cuenta (Pago Parcial):</span>
                                        <span class="font-mono">{{ config('cerisier.currency_symbol') }} {{ $sale->paid_amount }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-lg text-warning">
                                        <span>SALDO PENDIENTE:</span>
                                        <span class="font-mono">{{ config('cerisier.currency_symbol') }} {{ $sale->due_amount }}</span>
                                    </div>
                                @endif

                            </div>
                        </div>

                    </div>

                    <div class="modal-action">
                        {{-- Opcional: menú para elegir tamaño --}}
                        <div class="dropdown dropdown-top ml-2">
                            <div tabindex="0" role="button" class="btn btn-sm btn-primary"><i class="fa-solid fa-print"></i> Recibo <i class="fa-solid fa-sort-down"></i></div>
                            <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-40">
                                <li><a href="{{ route('sales.receipt_pdf', ['sale' => $sale->id, 'size' => 'letter']) }}" target="_blank">Carta</a></li>
                                <li><a href="{{ route('sales.receipt_pdf', ['sale' => $sale->id, 'size' => 'roll']) }}" target="_blank">Rollo</a></li>
                            </ul>
                        </div>
                        <div class="dropdown dropdown-top ml-2">
                            <div tabindex="0" role="button" class="btn btn-sm btn-primary"><i class="fa-solid fa-print"></i> Factura <i class="fa-solid fa-sort-down"></i></div>
                            <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-40">
                                <li><a href="{{ route('sales.invoice_pdf', ['sale' => $sale->id, 'size' => 'letter']) }}" target="_blank">Carta</a></li>
                                <li><a href="{{ route('sales.invoice_pdf', ['sale' => $sale->id, 'size' => 'roll']) }}" target="_blank">Rollo</a></li>
                            </ul>
                        </div>
                        <button wire:click="toggleForm" class="btn btn-sm">Cerrar</button>
                    </div>
                </div>
            </div>
        @endif
</div>
