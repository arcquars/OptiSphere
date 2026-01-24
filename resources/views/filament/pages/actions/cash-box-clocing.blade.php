<div class="grid grid-cols-3 gap-1">
    <div class="bg-orange-100 flex items-center justify-center">
        @if (strcmp($record->status, "closed") == 0)
        <b class="text-success text-2xl">CERRADO</b>    
        @else
        <b class="text-error text-2xl">ABIERTO</b>
        @endif
    </div>
    <div>
        <p><small><b>Sucursal</b></small></p>
        <p class="ml-1">{{ $record->branch->name }}</p>
    </div>
    <div>
        <p><small><b>Usuario</b></small></p>
        <p class="ml-1">{{ $record->user->name }}</p>
    </div>
    

    <div>
        <p><small><b>Fecha de apertura</b></small></p>
        <p class="ml-1">{{ $record->opening_time }}</p>
    </div>
    <div>
        <p><small><b>Fecha de cierre</b></small></p>
        <p class="ml-1">{{ $record->closing_time }}</p>
    </div>
    <div>
        <p><small><b>Balance Inicial</b></small></p>
        <p class="ml-1">{{ $record->initial_balance }}</p>
    </div>
    
    <div>
        <p><small><b>Balance Sistema</b></small></p>
        <p class="ml-1">{{ $record->expected_balance }}</p>
    </div>
    <div>
        <p><small><b>Balance Actual</b></small></p>
        <p class="ml-1">{{ $record->actual_balance }}</p>
    </div>
    <div class="@if($record->difference >= 0) text-success @else text-error @endif">
        <p><small><b>Diferencia</b></small></p>
        <p class="ml-1">{{ $record->difference }}</p>
    </div>

    @if(strcmp($record->status, "closed") == 0)
    <div class="col-span-3">
        <p><small><b>Nota</b></small></p>
        <p class="ml-1">{{ $record->notes }}</p>
    </div>
    @endif

    <div class="bg-base-100 rounded-lg shadow-sm p-2">
        <h3 class="font-bold text-base mb-3">Ventas al contado</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span>Efectivo</span><span class="font-semibold">{{ number_format($totals['sales']['cash'], 2) }}</span></div>
                <div class="flex justify-between"><span>Transferencia</span><span class="font-semibold">{{ number_format($totals['sales']['transfer'], 2) }}</span></div>
                <div class="flex justify-between"><span>QR</span><span class="font-semibold">{{ number_format($totals['sales']['qr'], 2) }}</span></div>
        </div>
    </div>
    <div class="bg-base-100 rounded-lg shadow-sm p-2">
        <h3 class="font-bold text-base mb-3">Cobros de crédito</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span>Efectivo</span><span class="font-semibold">{{ number_format($totals['credit_payments']['cash'], 2) }}</span></div>
            <div class="flex justify-between"><span>Transferencia</span><span class="font-semibold">{{ number_format($totals['credit_payments']['transfer'], 2) }}</span></div>
            <div class="flex justify-between"><span>QR</span><span class="font-semibold">{{ number_format($totals['credit_payments']['qr'], 2) }}</span></div>
        </div>
    </div>

    <div class="bg-base-100 rounded-lg shadow-sm p-2">
        <h3 class="font-bold text-base mb-3">Movimientos</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span>Ingresos</span><span class="font-semibold text-success">{{ number_format($totals['movements']['incomes'], 2) }}</span></div>
            <div class="flex justify-between"><span>Egresos</span><span class="font-semibold text-error">{{ number_format($totals['movements']['expenses'], 2) }}</span></div>
            <div class="divider my-1"></div>
            <div class="flex justify-between text-base font-bold">
                <span>Balance inicial</span>
                <span class="text-success">{{ number_format($record->initial_balance, 2) }}</span>
            </div>
            <div class="flex justify-between text-base font-bold">
                <span>Total del sistema</span>
                <span>{{ number_format($totals['system_total'], 2) }}</span>
            </div>
        </div>
    </div>
</div>