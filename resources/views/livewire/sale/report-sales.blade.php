<div x-data class="container mx-auto">
    <!-- Contenedor Principal del Reporte -->
    <div class="bg-base-100 p-6 rounded-box shadow-lg">

        <!-- 1. Cabecera y Filtros -->
        <form wire:submit="search">
        <div class="border-b border-base-300 pb-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end mb-1">
                <!-- Filtro Fecha Inicio -->
                <label class="w-full">
                    <div class="label"><span class="label-text">Fecha de Inicio</span></div>
                    <input type="date" wire:model.defer="dateStart" class="input @error('dateStart') input-error @enderror input-bordered w-full focus:outline-none" value="2025-09-01"/>
                    @error('dateStart')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </label>
                <!-- Filtro Fecha Fin -->
                <label class="w-full">
                    <div class="label"><span class="label-text">Fecha de Fin</span></div>
                    <input type="date" wire:model.defer="dateEnd" class="input @error('dateEnd') input-error @enderror input-bordered w-full focus:outline-none"/>
                    @error('dateEnd')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </label>
                <!-- Filtro Sucursal -->
                <label class="w-full">
                    <div class="label"><span class="label-text">Sucursal</span></div>
                    <select wire:model.defer="branchSelect" class="select select-bordered w-full focus:outline-none">
                        <option value="">Todas las sucursales</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </label>
                <!-- Filtro Tipo de Venta -->
                <label class="w-full">
                    <div class="label"><span class="label-text">Condición de Venta</span></div>
                    <select wire:model.defer="statusSelect" class="select select-bordered w-full focus:outline-none">
                        <option value="all">Todas</option>
                        <option value="{{ \App\Models\Sale::SALE_STATUS_PAID }}">{{ __('cerisier.'.\App\Models\Sale::SALE_STATUS_PAID) }}</option>
                        <option value="{{ \App\Models\Sale::SALE_STATUS_CREDIT }}">{{ __('cerisier.'.\App\Models\Sale::SALE_STATUS_CREDIT) }}</option>
                    </select>
                </label>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                @can('report sale admin')
                <!-- Filtro Tipo venta -->
                <label class="w-full">
                    <div class="label"><span class="label-text">Tipo venta</span></div>
                    <select wire:model.defer="typeSale" class="select select-bordered w-full focus:outline-none">
                        <option value="">Todas</option>
                        @foreach($typeSales as $ts)
                            <option value="{{ $ts }}">{{ $ts }}</option>
                        @endforeach
                    </select>
                </label>
                <!-- Filtro Tipo venta -->
                <label class="w-full">
                    <div class="label"><span class="label-text">Cliente</span></div>
                    <input wire:model.defer="clientSearch" type="text" class="input focus:outline-none">
                </label>
                <!-- Filtro Usuario -->
                <label class="w-full">
                    <div class="label"><span class="label-text">Usuarios</span></div>
                    <select wire:model.defer="userFilter" class="select select-bordered w-full focus:outline-none">
                        <option value="">Todos</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </label>
                @endcan

                <!-- <div class="col-span-1 md:col-span-2 lg:col-span-4"> -->
                <div class="col-span-1 md:col-span-2 lg:col-span-2">
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
                        <td>{{ $sale->date_sale->format('Y-m-d') }}</td>
                        <td>{{ $sale->customer->name }} <small>({{ $sale->customer->nit }})</small></td>
                        <td>{{ $sale->branch->name }}</td>
                        <td>
                            @switch($sale->sale_type)
                                @case(\App\Models\Price::TYPE_ESPECIAL)
                                <div class="badge badge-outline badge-info">{{ strtoupper($sale->sale_type) }}</div>
                                @break
                                @case(\App\Models\Price::TYPE_MAYORISTA)
                                <div class="badge badge-outline badge-primary">{{ strtoupper($sale->sale_type) }}</div>
                                @break
                                @default
                                <div class="badge badge-outline badge-neutral">{{ strtoupper($sale->sale_type) }}</div>
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
                            @if(strcmp($sale->status, \App\Models\Sale::SALE_STATUS_CREDIT) == 0)
                                @if(!$sale->is_paid)
                                <p class="text-error">{{ __('cerisier.'.$sale->status)  }}</p>
                                @else
                                <p class="text-success">{{ __('cerisier.'.$sale->status)  }}</p>
                                @endif
                            @else
                                {{ __('cerisier.'.$sale->status)  }}
                            @endif

                        </td>
                        <td>
                            @if($sale->use_promotion)
                                <div class="badge badge-soft badge-success">Sí</div>
                            @else
                                <div class="badge badge-soft badge-error">No</div>
                            @endif
                        </td>
                        <td class="text-right font-mono font-bold">{{ config('cerisier.currency_symbol') }} {{ $sale->final_total }}</td>
                        <td>
                            <div class="dropdown dropdown-bottom dropdown-end">
                                <div tabindex="0" role="button" class="btn btn-sm btn-primary m-1">Acciones <i class="fa-solid fa-sort-down"></i></div>
                                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm">
                                    <li><a @click="$dispatch('toggleViewSale', {saleId: '{{$sale->id}}'}); document.activeElement.blur(); return false;" class="text-primary">
                                            <i class="fa-solid fa-eye"></i> Ver
                                        </a>
                                    </li>
                                    @if(strcmp($sale->status, \App\Models\Sale::SALE_STATUS_CREDIT) == 0)
                                    <li>
                                        <a
                                            @click="$dispatch('toggleViewSalePayment', {saleId: '{{$sale->id}}'}); document.activeElement.blur(); return false;"
                                            class="text-primary" title="Registrar pago"
                                        >
                                            <i class="fa-solid fa-cash-register"></i> Reg. Pago
                                        </a>
                                    </li>
                                    @endif
                                    <li>
                                        <a href="{{ (isset($sale->siat_invoice_id))? route('sales.invoice_pdf', ['sale' => $sale->id]) : route('sales.receipt_pdf', ['sale' => $sale->id, 'size' => 'letter']) }}" onclick="document.activeElement.blur();" target="_blank" class="text-primary">
                                            @if(isset($sale->siat_invoice_id))
                                            <i class="fa-solid fa-print"></i> Imprimir factura
                                            @else
                                            <i class="fa-solid fa-print"></i> Imprimir recibo
                                            @endif
                                        </a>
                                    </li>
                                    @if($sale->can_edit)
                                        @if(strcmp($sale->siat_status, "void") == 0)
                                        <li>
                                            <a
                                                @click="$dispatch('toggleRevertirAnularSale', {saleId: '{{$sale->id}}'}); document.activeElement.blur(); return false;"
                                                class="text-emerald-500" title="Deshacer anulación factura"
                                            >
                                                <i class="fa-solid fa-link"></i> Revertir anulación factura
                                            </a>
                                        </li>
                                        @elseif(strcmp($sale->siat_status, "issued") == 0)
                                        <li>
                                            <a
                                                @click="$dispatch('toggleDeleteSale', {saleId: '{{$sale->id}}'}); document.activeElement.blur(); return false;"
                                                class="text-danger-500" title="Anular factura"
                                            >
                                                <i class="fa-solid fa-link-slash"></i> Anular factura
                                            </a>
                                        </li>
                                        @endif
                                    <li>
                                        <a
                                            @click="$dispatch('toggleDeleteSale', {saleId: '{{$sale->id}}', deleteSale: true}); document.activeElement.blur(); return false;"
                                            class="text-danger-500"
                                        >
                                            <i class="fa-solid fa-trash-can"></i> Eliminar
                                        </a>
                                    </li>
                                    @endif
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
