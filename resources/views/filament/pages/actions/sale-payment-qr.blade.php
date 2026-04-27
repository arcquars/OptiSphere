<div>
    <div class="px-1">
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2 text-center">
                <p class="mt-1 font-mono text-base text-green-600"><b>SALDO a pagar:</b> {{ $record->due_amount }}</p>
            </div>
            <div>
                <p class="p-qr-dd"><b>Cliente:</b></p>
                <p class="p-qr-dd text-right">{{ $record->customer->razon_social }}</p>
            </div>
            <div>
                <p class="p-qr-dd"><b>Fecha venta:</b></p>
                <p class="p-qr-dd text-right">{{ $record->date_sale->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="p-qr-dd"><b>M. Venta:</b></p>
                <p class="p-qr-dd text-right">{{ $record->final_total }}</p>
            </div>
            <div>
                <p class="p-qr-dd"><b>M. Pagado:</b></p>
                <p class="p-qr-dd text-right">{{ $record->paid_amount }}</p>
            </div>
        </div>
    </div>
</div>
