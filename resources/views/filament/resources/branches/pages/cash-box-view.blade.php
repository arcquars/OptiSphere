<x-filament-panels::page>
    <div class="@if($cashBoxClosing?->status == \App\Models\CashBoxClosing::STATUS_OPEN) bg-primary @else bg-error @endif rounded-2xl shadow-sm p-4 md:p-6">
        <div class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="w-full md:w-1/4">
                <p class="block text-sm font-semibold mb-1">Caja: <span class="font-normal">@if($cashBoxClosing?->status == \App\Models\CashBoxClosing::STATUS_OPEN) <i class="fa-solid fa-lock-open"></i> Abierta @else <i class="fa-solid fa-lock"></i> Cerrada @endif</span></p>
            </div>
            <div class="w-full md:w-1/4">
                <p class="block text-sm font-semibold mb-1">Usuario: <span class="font-normal">{{ $cashBoxClosing?->user->name }}</span></p>
            </div>
            <div class="w-full md:w-1/4">
                <p class="block text-sm font-semibold mb-1">Desde: <span class="font-normal">{{ $cashBoxClosing?->opening_time }}</span></p>

            </div>
            <div class="w-full md:w-1/4">
                <p class="block text-sm font-semibold mb-1">Hasta: <span class="font-normal">{{ $cashBoxClosing?->closing_time }}</span></p>

            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-base-100 rounded-2xl shadow-sm p-5">
            <h3 class="font-bold text-base mb-3">Ventas al contado</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Efectivo</span><span class="font-semibold">{{ number_format($this->totals['sales']['cash'], 2) }}</span></div>
                <div class="flex justify-between"><span>Transferencia</span><span class="font-semibold">{{ number_format($this->totals['sales']['transfer'], 2) }}</span></div>
                <div class="flex justify-between"><span>QR</span><span class="font-semibold">{{ number_format($this->totals['sales']['qr'], 2) }}</span></div>
            </div>
        </div>

        <div class="bg-base-100 rounded-2xl shadow-sm p-5">
            <h3 class="font-bold text-base mb-3">Cobros de crédito</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Efectivo</span><span class="font-semibold">{{ number_format($this->totals['credit_payments']['cash'], 2) }}</span></div>
                <div class="flex justify-between"><span>Transferencia</span><span class="font-semibold">{{ number_format($this->totals['credit_payments']['transfer'], 2) }}</span></div>
                <div class="flex justify-between"><span>QR</span><span class="font-semibold">{{ number_format($this->totals['credit_payments']['qr'], 2) }}</span></div>
            </div>
        </div>

        <div class="bg-base-100 rounded-2xl shadow-sm p-5">
            <h3 class="font-bold text-base mb-3">Movimientos</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Ingresos</span><span class="font-semibold text-success">{{ number_format($this->totals['movements']['incomes'], 2) }}</span></div>
                <div class="flex justify-between"><span>Egresos</span><span class="font-semibold text-error">{{ number_format($this->totals['movements']['expenses'], 2) }}</span></div>
                <div class="divider my-1"></div>
                <div class="flex justify-between text-base font-bold">
                    <span>Balance inicial</span>
                    <span class="text-success">{{ number_format($cashBoxClosing->initial_balance, 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-bold">
                    <span>Total del sistema</span>
                    <span>{{ number_format($this->totals['system_total'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Cierre -->
    <div class="bg-base-100 rounded-2xl shadow-sm p-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
            <div>
                <div class="flex justify-between mb-1">
                    <span>Efectivo contado por cajero</span>
                    <span class="font-semibold">{{ number_format($this->cashBc->actual_balance, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Diferencia</span>
                    <span class="font-semibold">{{ number_format($this->cashBc->difference, 2) }}</span>
                </div>

            </div>
            <div>
                <p class="block text-base font-semibold mb-1">Notas</p>
                <p class="p-1">{{ $cashBc->notes }}</p>
            </div>

            <button
                class="btn btn-warning"
                wire:click="printPdf"
                @if(strcmp($this->cashBc->status, \App\Models\CashBoxClosing::STATUS_OPEN) == 0) disabled @endif
            >
                Imprimir Pdf
            </button>
        </div>

    </div>
</x-filament-panels::page>
