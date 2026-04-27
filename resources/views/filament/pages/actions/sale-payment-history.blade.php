<div>
    @if($isQrActive)
    <h1 class="text-error text-center font-bold mb-2">Existe un QR activo para el pago de esta venta a credito</h1>
    @endif
    <div class="grid grid-cols-2 gap-1">
        <div>
            <p class="text-sm"><b>Fecha de venta: </b>{{ $record->id }} {{ $record->date_sale->format('Y-m-d') }}</p>
        </div>
        <div>
            <p class="text-sm"><b>Usuario: </b> {{ $record->user->name }}</p>
        </div>
        <div>
            <p class="text-sm"><b>Total venta: </b> {{ $record->final_total }}</p>
        </div>
        <div>
            <p class="text-sm @if(!$record->is_paid) text-error @else text-success @endif">
                <b>Saldo: </b> {{ number_format($record->due_amount, 2) }}
            </p>
        </div>
    </div>
    @if(count($payments) > 0)
    <div class="overflow-x-auto">
        <table class="table table-sm">
            <!-- head -->
            <thead>
            <tr>
                <th></th>
                <th>Usuario</th>
                <th>Fecha</th>
                <th>Cantidad</th>
                <th>Saldo</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($payments as $i => $payment)
            <tr class="@if($payment->id == $record->lastPayment->id) bg-orange-200 @endif">
                <th>{{ $i++ }}</th>
                <td>{{ $payment->user->name }}</td>
                <td>{{ $payment->created_at }}</td>
                <td>{{ $payment->amount }}</td>
                <td>{{ $payment->residue }}</td>
            </tr>    
            @endforeach
            
            </tbody>
        </table>
    </div>
    @else
    <h2 class="text-center text-info my-2">Sin registro de pagos</h2>
    @endif
</div>