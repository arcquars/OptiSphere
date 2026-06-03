<?php
use App\Models\WarehouseIncome;
?>
<x-filament-panels::page>
    <div class="p-4 bg-white shadow rounded-xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
        <div class="flex justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-400 uppercase tracking-wider">Detalles de la Consulta</h2>
            </div>
            <div>
                {{-- @if(strcmp($action, "INGRESO") == 0 && auth()->user()->hasRole('admin')) --}}
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
                {{-- @endif --}}
                <x-filament::button 
                    color="primary" 
                    size="xs"
                    icon="heroicon-m-printer"
                    tag="a"
                    target="_blank"
                    :disabled="(strcmp($warehouseM->status, WarehouseIncome::STATUS_VOID) == 0)"
                    {{-- href="{{ route('export.pdf.history.movement', ['movement' => $action, 'movement_id' => $warehouseM->id, 'type' => $type]) }}" --}}
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
    
</x-filament-panels::page>
