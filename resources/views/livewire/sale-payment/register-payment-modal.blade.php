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
                <h2 class="font-bold text-lg mb-4">Registro de pagos parciales</h2>
                <h3 class="font-bold text-base mb-4">Venta <span class="font-medium"># {{ $sale->id }}</span> <span class="font-normal">|</span> Sucursal <span class="font-medium">{{ $sale->branch->name }}</span></h3>

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
                                <span>{{ strtoupper($sale->sale_type) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-money-bill text-primary"></i>
                            <div>
                                <span class="font-semibold">Total:</span>
                                <span>{{ $sale->final_total }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-user text-primary"></i>
                            <div>
                                <span class="font-semibold">Usuario que registro:</span>
                                <span>{{ $sale->user->name }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100">
                        <table class="table">
                            <!-- head -->
                            <thead>
                            <tr>
                                <th></th>
                                <th>Usuario</th>
                                <th>M. pago</th>
                                <th>Fecha</th>
                                <th>Nota</th>
                                <th>Cantidad</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($payments as $index => $payment)
                                <tr>
                                    <th>{{ $index+1 }}</th>
                                    <td>{{ $payment->user->name }}</td>
                                    <td>
                                        @switch($payment->payment_method)
                                            @case(\App\Models\SalePayment::METHOD_TRANSFER)
                                            <div class="badge badge-outline badge-secondary">
                                                <i class="fa-solid fa-credit-card mr-1"></i>{{ $payment->payment_method }}
                                            </div>
                                            @break
                                            @case(\App\Models\SalePayment::METHOD_QR)
                                            <div class="badge badge-outline badge-info">
                                                <i class="fa-solid fa-qrcode mr-1"></i>{{ $payment->payment_method }}
                                            </div>
                                            @break
                                            @default
                                            <div class="badge badge-outline badge-success">
                                                <i class="fa-solid fa-money-bill-wave mr-1"></i>{{ $payment->payment_method }}
                                            </div>
                                        @endswitch
                                    </td>
                                    <td>{{ $payment->created_at }}</td>
                                    <td>{{ $payment->notes }}</td>
                                    <td class="text-right">{{ $payment->amount }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $payments->links() }}

                </div>

                <div class="text-right">
                    <p class="text-sm">Total Pagos: <span>{{ $sale->paid_amount }} BOB</span></p>
                    <p class="text-sm">Total VENTA: <span>{{ $sale->final_total }} BOB</span></p>
                    <p class="text-sm text-error">FALTANTE: <span>{{ $sale->final_total - $sale->paid_amount }} BOB</span></p>
                </div>

                <form wire:submit="registerPayment">
                    @if(!$this->sale->is_paid)
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Registrar pago</legend>
                                <input type="number" wire:model="amountPayment" class="input input-primary focus-within:outline-none" step="0.01" />
                                <p class="label">Maximo: {{ $sale->final_total - $sale->total_partial_payments }}</p>
                                @error('amountPayment')
                                <p class="text-sm text-error">{{ $message }}</p>
                                @enderror
                            </fieldset>
                        </div>
                        <div>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Registrar pago</legend>
                                <textarea wire:model.defer="notes" class="textarea textarea-primary focus-within:outline-none" placeholder=""></textarea>
                            </fieldset>

                        </div>
                        <div>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Registrar pago</legend>
                                <label for="payment-type-c">
                                    <input
                                        id="payment-type-c"
                                        type="radio" name="radio-12" wire:model.defer="paymentType"
                                        value="{{ \App\Models\SalePayment::METHOD_CASH }}"
                                        class="radio radio-primary" />
                                    <i class="fa-solid fa-money-bill-wave mr-2"></i>{{ \App\Models\SalePayment::METHOD_CASH }}
                                </label>
{{--                                <br>--}}
                                <label for="payment-type-t">
                                    <input
                                        id="payment-type-t"
                                        type="radio" name="radio-12" wire:model.defer="paymentType"
                                        value="{{ \App\Models\SalePayment::METHOD_TRANSFER }}"
                                        class="radio radio-primary mt-2" />
                                    <i class="fa-solid fa-credit-card mr-2"></i>{{ \App\Models\SalePayment::METHOD_TRANSFER }}
                                </label>
                            </fieldset>
                        </div>
                    </div>
                    @endif


                    @if($this->sale->is_paid)
                        <h3 class="text-2xl text-success font-bold text-center">Se completo el pago de esta venta.</h3>
                    @endif


                <div class="modal-action">
                    @if(!$this->sale->is_paid)
                    <button type="submit" class="btn btn-sm btn-warning">Registrar</button>
                    @endif
                    <button type="button" wire:click="toggleForm" class="btn btn-sm">Cerrar</button>
                </div>
                </form>
            </div>
        </div>
    @endif
</div>
