<div class="space-y-4">
    @if($branchId)
        <h3>Caja abierta: {{ $cashBoxClosing?->opening_time }} | Balance inicial: {{ $cashBoxClosing?->initial_balance }}</h3>
    <!-- Filtros / cabecera -->
    <div class="bg-base-100 rounded-2xl shadow-sm p-4 md:p-6">
        <div class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="w-full md:w-1/4">
                <label class="block text-sm font-semibold mb-1">Desde</label>
                <input type="datetime-local" class="input input-bordered w-full"
                       wire:model.live="from" readonly>
            </div>
            <div class="w-full md:w-1/4">
                <label class="block text-sm font-semibold mb-1">Hasta</label>
                <input type="datetime-local" class="input input-bordered w-full"
                       wire:model.live="until" readonly>
            </div>

            @if(auth()->user()->hasRole('admin'))
                <div class="w-full md:w-1/4">
                    <label class="block text-sm font-semibold mb-1">Usuario</label>
                    <select class="select select-bordered w-full" wire:model.live="userId">
                        <option value="{{ auth()->id() }}">(Yo) {{ auth()->user()->name }}</option>
                        @foreach(\App\Models\User::orderBy('name')->get() as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="w-full md:w-1/4 md:text-right">
                <button type="button" class="btn btn-primary" wire:click="refreshTotals" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="refreshTotals">Actualizar</span>
                    <span wire:loading wire:target="refreshTotals" class="loading loading-spinner loading-xs"></span>
                </button>
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
        <form wire:submit="close" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-semibold mb-1">Efectivo contado por cajero <span class="text-error">*</span></label>
                <input type="number" step="0.01" class="input input-bordered focus-within:outline-none w-full"
                       wire:model.blur="closingAmount" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Notas</label>
                <textarea class="textarea textarea-bordered focus-within:outline-none w-full" rows="2" wire:model="notes" placeholder="Observaciones del cierre"></textarea>
            </div>
            <div class="md:text-right">
                <button type="submit" class="btn btn-warning"
{{--                        wire:click="close"--}}
                        wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="close">Cerrar caja</span>
                    <span wire:loading wire:target="close" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </form>

        @php
            $diff = (float) ($closingAmount - $this->totals['system_total']);
        @endphp
        <div class="mt-4 text-sm">
            <div class="flex justify-between">
                <span>Diferencia</span>
                <span class="{{ $diff === 0.0 ? 'text-success' : ($diff > 0 ? 'text-warning' : 'text-error') }} font-semibold">
                    {{ number_format($diff, 2) }}
                </span>
            </div>
        </div>
    </div>

    @endif
</div>
