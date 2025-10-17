<div x-data class="container mx-auto">
    <!-- Contenedor Principal del Reporte -->
    <div class="bg-base-100 p-6 rounded-box shadow-lg">

        <!-- 1. Cabecera y Filtros -->
        <form wire:submit="search">
        <div class="border-b border-base-300 pb-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Filtro Fecha Inicio -->
                <label class="form-control w-full">
                    <div class="label"><span class="label-text">Fecha de Inicio</span></div>
                    <input type="date" wire:model.defer="dateStart" class="input @error('dateStart') input-error @enderror input-bordered w-full focus:outline-none" value="2025-09-01"/>
                    @error('dateStart')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </label>
                <!-- Filtro Fecha Fin -->
                <label class="form-control w-full">
                    <div class="label"><span class="label-text">Fecha de Fin</span></div>
                    <input type="date" wire:model.defer="dateEnd" class="input @error('dateEnd') input-error @enderror input-bordered w-full focus:outline-none"/>
                    @error('dateEnd')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </label>
                <!-- Filtro Sucursal -->
                <label class="form-control w-full">
                    <div class="label"><span class="label-text">Sucursal</span></div>
                    <select wire:model.defer="branchSelect" class="select select-bordered w-full focus:outline-none">
                        <option value="">Todas las sucursales</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </label>
                <!-- Filtro Tipo de Venta -->
                <label class="form-control w-full">
                    <div class="label"><span class="label-text">Condición de Venta</span></div>
                    <select wire:model.defer="statusSelect" class="select select-bordered w-full focus:outline-none">
                        <option value="all">Todas</option>
                        <option value="{{ \App\Models\Sale::SALE_STATUS_PAID }}">{{ __('cerisier.'.\App\Models\Sale::SALE_STATUS_PAID) }}</option>
                        <option value="{{ \App\Models\Sale::SALE_STATUS_CREDIT }}">{{ __('cerisier.'.\App\Models\Sale::SALE_STATUS_CREDIT) }}</option>
                    </select>
                </label>
                <div class="col-span-1 md:col-span-2 lg:col-span-4 flex justify-end">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Generar Reporte</button>
                </div>
            </div>
        </div>
        </form>

        <span wire:loading class="loading loading-spinner text-primary ml-2"></span>

        <!-- 2. Resumen de KPIs (Indicadores Clave) -->
        @role('admin')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat bg-base-200 rounded-box">
                <div class="stat-figure text-primary"><i class="fa-solid fa-dollar-sign fa-2x"></i></div>
                <div class="stat-title">Ventas Totales</div>
                <div class="stat-value">{{ config('cerisier.currency_symbol') }} {{ $totalSales }}</div>
            </div>
            <div class="stat bg-base-200 rounded-box">
                <div class="stat-figure text-info"><i class="fa-solid fa-file-invoice-dollar fa-2x"></i></div>
                <div class="stat-title">Ventas a Crédito</div>
                <div class="stat-value">{{ config('cerisier.currency_symbol') }} {{ $creditSales }}</div>
            </div>
            <div class="stat bg-base-200 rounded-box">
                <div class="stat-figure text-success"><i class="fa-solid fa-receipt fa-2x"></i></div>
                <div class="stat-title">Nº de Transacciones</div>
                <div class="stat-value">{{ $transactionCount }}</div>
            </div>
            <div class="stat bg-base-200 rounded-box">
                <div class="stat-figure text-accent"><i class="fa-solid fa-ticket fa-2x"></i></div>
                <div class="stat-title">Promociones Usadas</div>
                <div class="stat-value">{{ $promoCount }}</div>
            </div>
        </div>
        @endrole

        @if(count($sales) > 0)
        <!-- 3. Tabla de Resultados -->
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Sucursal</th>
                    <th>Tipo Venta</th>
                    <th>Tipo Pago</th>
                    <th>Estado</th>
                    <th>Promoción</th>
                    <th class="text-right">Total</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td class="font-bold">{{ $sale->id }}</td>
                        <td>{{ $sale->date_sale }}</td>
                        <td>{{ $sale->customer->name }} <small>({{ $sale->customer->nit }})</small></td>
                        <td>{{ $sale->branch->name }}</td>
                        <td>
                            @switch($sale->sale_type)
                                @case(\App\Models\Price::TYPE_ESPECIAL)
                                <div class="badge badge-info">{{ strtoupper($sale->sale_type) }}</div>
                                @break
                                @case(\App\Models\Price::TYPE_MAYORISTA)
                                <div class="badge badge-primary">{{ strtoupper($sale->sale_type) }}</div>
                                @break
                                @default
                                <div class="badge badge-neutral">{{ strtoupper($sale->sale_type) }}</div>
                            @endswitch
                        </td>
                        <td>

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

                        </td>
                        <td>
                            {{ __('cerisier.'.$sale->status)  }}
                        </td>
                        <td>
                            @if($sale->use_promotion)
                                <div class="badge badge-accent">Sí</div>
                            @else
                                <div class="badge badge-ghost">No</div>
                            @endif
                        </td>
                        <td class="text-right font-mono font-bold">{{ config('cerisier.currency_symbol') }} {{ $sale->final_total }}</td>
                        <td>
                            <div class="dropdown dropdown-bottom dropdown-end">
                                <div tabindex="0" role="button" class="btn m-1">Acciones <i class="fa-solid fa-sort-down"></i></div>
                                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm">
                                    <li><a @click="$dispatch('toggleViewSale', {saleId: '{{$sale->id}}'}); return false;" class="text-primary">
                                            <i class="fa-solid fa-eye"></i> Ver
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('sales.receipt_pdf', ['sale' => $sale->id, 'size' => 'letter']) }}" target="_blank" class="text-primary">
                                            <i class="fa-solid fa-print"></i> Imprimir recibo
                                        </a>
                                    </li>
                                    <li><a @click="$dispatch('toggleDeleteSale', {saleId: '{{$sale->id}}'}); return false;" class="text-danger-500"><i class="fa-solid fa-trash-can"></i> Eliminar</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <!-- 4. Paginación -->
            <div class="my-4">
                {{ $sales->links() }}
            </div>
            <br>
            <br>
        </div>



        @else
            <p class="text-lg text-success">Sin Ventas en la busqueda</p>
        @endif


    </div>

    <!-- Modal de confirmación -->
    <x-modal id="voidSaleModal" title="Anular venta">
        <div class="space-y-3">
            <p class="text-sm">Esta acción revertirá el inventario y marcará la venta como anulada.</p>
            <textarea class="textarea textarea-bordered w-full" wire:model="voidReason" placeholder="Motivo (opcional)"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" wire:click="$dispatch('close-modal', {id:'voidSaleModal'})">Cancelar</button>
                <button type="button" class="btn btn-error" wire:click="voidSale">Anular</button>
            </div>
        </div>
    </x-modal>
</div>
