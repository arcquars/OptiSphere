<?php
use App\Models\WarehouseIncome;
?>
<x-filament-panels::page>
    @livewire('warehouse.edit-warehouse-stock-item-modal')
    {{-- Cabecera con los parámetros actuales --}}
    <div class="p-4 bg-white shadow rounded-xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
        <div class="flex justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-400 uppercase tracking-wider">Detalles de la Consulta</h2>
            </div>
            <div>
                @if(strcmp($action, "INGRESO") == 0 && auth()->user()->hasRole('admin'))
                    @livewire('warehouse.void-wharehouse-income-modal', ['warehouseInvoiceId' => $warehouse_m_id])
                    <x-filament::button 
                        color="danger" 
                        size="xs"
                        icon="heroicon-s-no-symbol"
                        tag="button"
                        :disabled="(strcmp($warehouse_m->status, WarehouseIncome::STATUS_VOID) == 0)"
                        x-on:click="$dispatch('open-void-warehouse-invoice-modal')"
                    >
                        Anular Entrega
                    </x-filament::button>
                    <x-filament::button 
                        color="primary" 
                        size="xs"
                        icon="heroicon-m-paper-airplane"
                        tag="button"
                        :disabled="(strcmp($warehouse_m->status, WarehouseIncome::STATUS_VOID) == 0)"
                        x-on:click="$dispatch('open-modal', { id: 'send-to-branch-modal' })"
                    >
                        Enviar a Sucursal
                    </x-filament::button>
                @endif
                <x-filament::button 
                    color="primary" 
                    size="xs"
                    icon="heroicon-m-printer"
                    tag="a"
                    target="_blank"
                    :disabled="(strcmp($warehouse_m->status, WarehouseIncome::STATUS_VOID) == 0)"
                    href="{{ route('export.pdf.history.movement', ['movement' => $action, 'movement_id' => $warehouse_m_id, 'type' => $type]) }}"
                >
                    Imprimir
                </x-filament::button>
            </div>
        </div>
        
        <h4><b>Registrado por:</b> {{ $userM->name }}</h4>
        <div class="flex flex-wrap gap-3 mt-3">
            <div class="flex items-center gap-2 badge badge-{{ $bgAction }}">
                <i class="fa-regular fa-chess-pawn"></i>
                <span>
                    Acción : {{ $warehouse_m_id . ".-" . $action }} 
                    <span class="text-red-700">
                        @if(strcmp($action, "INGRESO") == 0 && strcmp($warehouse_m->status, WarehouseIncome::STATUS_VOID) == 0) 
                        (ANULADO) 
                        @endif
                    </span>
                </span>
            </div>
            <div class="flex items-center gap-2 badge badge-soft badge-primary">
                <i class="fa-solid fa-warehouse"></i>
                <span>Almacén: {{ $warehouse_name }}</span>
            </div>
            @if(strcmp($action, "ENTREGA") == 0)
            <div class="flex items-center gap-2 badge badge-soft badge-primary">
                <i class="fa-solid fa-warehouse"></i>
                <span>Se entrego a: {{ $warehouse_m->branch->name }}</span>
            </div>
            @endif
            @if(strcmp($action, "DEVOLUCION") == 0)
            <div class="flex items-center gap-2 badge badge-soft badge-primary">
                <i class="fa-solid fa-warehouse"></i>
                <span>Devolvio: {{ $warehouse_m->branch->name }}</span>
            </div>
            @endif
            <div class="flex items-center gap-2 badge badge-soft badge-info">
                <i class="fa-solid fa-tags"></i>
                <span>Tipo: {{ $type }}</span>
            </div>
            <div class="flex items-center gap-2 badge badge-soft badge-info">
                <i class="fa-solid fa-barcode"></i>
                <span>Código: {{ $baseCode }}</span>
            </div>
            <div class="flex items-center gap-2 badge badge-soft badge-info">
                <i class="fa-regular fa-clock"></i>
                <span>Fecha : {{ $dateMovement }}</span>    
            </div>
            
        </div>
    </div>


    <div class="table-container border border-gray-200 rounded-lg relative select-none overflow-x-auto">
        <table id="t-matrix" x-ref="table" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky-header">
            <tr id="t-matrix">
                <th scope="col" class="">
                </th>
                @foreach ($uniqueCylinders as $i => $cylinder)
                    <th scope="col" class="px-1 py-1 text-center text-xs font-semibold text-white uppercase tracking-wider bg-success
                        @if($i == 8 || $i == 16) border-r-2 border-r-zinc-600 @endif
                    ">
                        {{ number_format($cylinder, 2) }}
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody
                class="bg-white divide-y divide-gray-200"
            >
            @foreach($matrix as $j => $row)
                <tr class="hover:bg-gray-50 @if($j == 4 || $j == 8 || $j == 16) border-b-2 border-b-zinc-600 @endif">
                    @foreach($row as $i => $opticalProperty)
                        @if($opticalProperty)
                            <td class="px-1 py-1 whitespace-nowrap text-xs text-center font-medium text-white
                                @if(strcmp($type, "+") == 0) bg-blue-500 @else bg-red-500 @endif
                                " >
                                {{ number_format($opticalProperty['sphere'], 2) }}
                            </td>
                            @break
                        @endif
                    @endforeach
                    @foreach($row as $i => $opticalProperty)
                        <td
                            title="{{ $opticalProperty['description'] }}"
                            data-cell-id="{{ $opticalProperty['id'] }}"
                            data-cell-amount="{{ $opticalProperty['amount'] }}"
                            @click="toggleCell({{ (int) $opticalProperty['id'] }})"
                            
                            class="px-1 py-1 whitespace-nowrap text-xs font-medium text-center border-t border-r
                            border-l
                            @if($j == 4 || $j == 8 || $j == 16) border-b-2 border-b-zinc-600 @else border-b @endif
                            @if($i == 8 || $i == 16) border-r-2 border-r-zinc-600 @endif

                            @if($j == 12 && $i == 4) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 13 && $i == 5) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 12 && $i == 12) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 13 && $i == 13) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 12 && $i == 20) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 13 && $i == 21) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 20 && $i == 4) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 21 && $i == 5) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 20 && $i == 12) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 21 && $i == 13) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 20 && $i == 20) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 21 && $i == 21) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif
                                "
                        >
                        @if(!empty($opticalProperty['amount']))
                            @if(auth()->user()->hasRole('admin') && strcmp($action, "INGRESO") == 0 && strcmp($warehouse_m->status, WarehouseIncome::STATUS_ACTIVE) == 0)
                        <a 
                            href="#" 
                            title="Editar" 
                            class="text-warning p-2 no-underline" 
                            x-on:click="$dispatch('open-edit-warehouse-stock-modal', { historyId: '{{ $this->warehouse_m_id }}', action: '{{ $this->action }}', productId: '{{ $opticalProperty['id'] }}', warehouseId: '{{ $warehouse_id }}' })"
                        >
                            {{ $opticalProperty['amount'] }}
                        </a>
                            @else
                                <p>{{ $opticalProperty['amount'] }}</p>
                            @endif
                        @else
                            @if(auth()->user()->hasRole('admin') && strcmp($warehouse_m->status, WarehouseIncome::STATUS_ACTIVE) == 0)
                            <a 
                                href="#" 
                                title="Editar" 
                                class="text-primary p-2 no-underline" 
                                x-on:click="$dispatch('open-edit-warehouse-stock-modal', { historyId: '{{ $this->warehouse_m_id }}', action: '{{ $this->action }}', productId: '{{ $opticalProperty['id'] }}', warehouseId: '{{ $warehouse_id }}' })"
                            >
                                <i class="fa-solid fa-plus"></i>
                            </a>
                            @endif
                        @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>


    {{-- Definición del Modal Enviar a Sucursal --}}
    <x-filament::modal id="send-to-branch-modal" width="md">
        <x-slot name="heading">
            Enviar a Sucursal
        </x-slot>

        <x-slot name="description">
            Selecciona la sucursal de destino para el código <b>{{ $baseCode }}</b>.
        </x-slot>

        <div class="space-y-4 py-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sucursal Destino</label>
                <x-filament::input.wrapper size="xs">
                    <x-filament::input.select wire:model="selectedBranchId" size="xs">
                        <option value="">Seleccione una sucursal...</option>
                        @foreach(\App\Models\Branch::where('is_active', 1)->pluck('name', 'id') as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                
                @error('selectedBranchId') <span class="text-danger-600 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button 
                    color="gray" 
                    size="sm"
                    x-on:click="close"
                >
                    Cancelar
                </x-filament::button>

                <x-filament::button 
                    color="primary" 
                    size="sm"
                    wire:click="sendToBranch"
                >
                    Confirmar Envío
                </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <div wire:loading.flex 
        class="fixed inset-0 z-[10000] items-center justify-center bg-slate-900/60 backdrop-blur-md">
        
        <div class="bg-base-100 p-10 rounded-3xl shadow-2xl flex flex-col items-center gap-6 border border-white/10">
            {{-- Spinner de DaisyUI --}}
            <span class="loading loading-spinner w-16 text-primary"></span>
            
            <div class="text-center">
                <p class="text-xl font-black text-base-content tracking-tight">Procesando solicitud</p>
                <p class="text-sm text-base-content/60">Por favor, no cierre esta ventana</p>
            </div>
        </div>
    </div>
    
</x-filament-panels::page>
