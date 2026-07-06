<?php
use App\Models\WarehouseIncome;
use App\Models\WarehouseRefund;
?>
<x-filament-panels::page>
    <div class="p-4 bg-white shadow rounded-xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
        <div class="flex justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-400 uppercase tracking-wider">Detalles de la Consulta</h2>
            </div>
            <div>
                @if(strcmp($action, "INGRESO") == 0 && auth()->user()->hasRole('admin'))
                    @livewire('warehouse.void-wharehouse-income-modal', ['warehouseInvoiceId' => $warehouseM->id])
                    <x-filament::button 
                        color="danger" 
                        size="xs"
                        icon="heroicon-s-no-symbol"
                        tag="button"
                        :disabled="(strcmp($warehouseM->status, WarehouseIncome::STATUS_VOID) == 0)"
                        x-on:click="$dispatch('open-void-warehouse-invoice-modal')"
                    >
                        Anular Ingreso
                    </x-filament::button>
                    <x-filament::button 
                        color="primary" 
                        size="xs"
                        icon="heroicon-m-paper-airplane"
                        tag="button"
                        :disabled="(strcmp($warehouseM->status, WarehouseIncome::STATUS_VOID) == 0)"
                        x-on:click="$dispatch('open-modal', { id: 'send-to-branch-modal' })"
                    >
                        Enviar a Sucursal
                    </x-filament::button>
                @endif

                @if(strcmp($action, "ENTREGA") == 0 && auth()->user()->hasRole('admin'))
                    <x-filament::button
                        color="primary"
                        size="xs"
                        icon="heroicon-m-arrows-right-left"
                        tag="button"
                        x-on:click="$dispatch('open-modal', { id: 'transfer-to-branch-modal' })"
                    >
                        Enviar a Sucursal
                    </x-filament::button>
                @endif

                @if(strcmp($action, "DEVOLUCION") == 0 && auth()->user()->hasRole('admin'))
                    @livewire('warehouse.void-wharehouse-refund-modal', ['warehouseRefundId' => $warehouseM->id])
                    <x-filament::button 
                        color="danger" 
                        size="xs"
                        icon="heroicon-s-no-symbol"
                        tag="button"
                        :disabled="(strcmp($warehouseM->status, WarehouseRefund::STATUS_VOID) == 0)"
                        x-on:click="$dispatch('open-void-warehouse-refund-modal')"
                    >
                        Anular Devolucion
                    </x-filament::button>
                @endif

                <x-filament::button 
                    color="primary" 
                    size="xs"
                    icon="heroicon-m-printer"
                    tag="a"
                    target="_blank"
                    :disabled="(strcmp($warehouseM->status, WarehouseIncome::STATUS_VOID) == 0)"
                    {{-- href="{{ route('export.pdf.history.movement', ['movement' => $action, 'movement_id' => $warehouseM->id, 'type' => $type]) }}" --}}
                    href="{{ route('export.pdf.history.movement.list', ['movement' => $action, 'movement_id' => $warehouseM->id]) }}"
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
                    Acción : {{ $warehouseM->id . ".- " . $action }} 
                    <span class="text-red-700">
                        @if(strcmp($action, "INGRESO") == 0 && strcmp($warehouseM->status, WarehouseIncome::STATUS_VOID) == 0) 
                        (ANULADO) 
                        @endif
                    </span>
                </span>
            </div>
            <div class="flex items-center gap-2 badge badge-soft badge-primary">
                <i class="fa-solid fa-warehouse"></i>
                <span>Almacén: {{ $warehouseM->warehouse->name }}</span>
            </div>
            @if(strcmp($action, "ENTREGA") == 0)
            <div class="flex items-center gap-2 badge badge-soft badge-primary">
                <i class="fa-solid fa-warehouse"></i>
                <span>Se entrego a: {{ $warehouseM->branch->name }}</span>
            </div>
            @endif
            @if(strcmp($action, "DEVOLUCION") == 0)
            <div class="flex items-center gap-2 badge badge-soft badge-primary">
                <i class="fa-solid fa-warehouse"></i>
                <span>Devolvio: {{ $warehouseM->branch->name }}</span>
            </div>
            @endif
            <div class="flex items-center gap-2 badge badge-soft badge-info">
                <i class="fa-regular fa-clock"></i>
                <span>Fecha : {{ $warehouseM->created_at }}</span>    
            </div>
            
        </div>
    </div>

    {{-- Tabla unificada de movimientos --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        {{ $this->table }}
    </div>
    

    {{-- Definición del Modal Enviar a Sucursal --}}
    <x-filament::modal id="send-to-branch-modal" width="md">
        <x-slot name="heading">
            Enviar a Sucursal
        </x-slot>

        <x-slot name="description">
            Selecciona la sucursal de destino para el código.
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

    {{-- Modal Enviar a Sucursal (traslado sucursal origen -> sucursal destino, sobre una ENTREGA) --}}
    @if(strcmp($action, "ENTREGA") == 0 && auth()->user()->hasRole('admin'))
    <x-filament::modal id="transfer-to-branch-modal" width="md">
        <x-slot name="heading">
            Enviar a otra Sucursal
        </x-slot>

        <x-slot name="description">
            Se trasladarán todos los productos de esta entrega desde la sucursal
            <b>{{ $warehouseM->branch?->name ?? '' }}</b> a la sucursal destino seleccionada.
        </x-slot>

        <div class="space-y-4 py-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sucursal Destino</label>
                <x-filament::input.wrapper size="xs">
                    <x-filament::input.select wire:model="destinationBranchId" size="xs">
                        <option value="">Seleccione una sucursal...</option>
                        @foreach(\App\Models\Branch::where('is_active', 1)->where('id', '!=', $warehouseM->branch_id)->pluck('name', 'id') as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>

                @error('destinationBranchId') <span class="text-danger-600 text-xs">{{ $message }}</span> @enderror
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
                    wire:click="transferToBranch"
                >
                    Confirmar Traslado
                </x-filament::button>
        </x-slot>
    </x-filament::modal>
    @endif
</x-filament-panels::page>
