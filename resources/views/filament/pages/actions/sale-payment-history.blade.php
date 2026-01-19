<div>
    <div class="grid grid-cols-2 gap-1">
        <div>
            <p class="text-sm"><b>Fecha de venta: </b> {{ $salePayment->sale->date_sale }}</p>
        </div>
        <div>
            <p class="text-sm"><b>Usuario: </b> {{ $salePayment->sale->user->name }}</p>
        </div>
        <div>
            <p class="text-sm"><b>Total venta: </b> {{ $salePayment->sale->total_amount }}</p>
        </div>
        <div>
            <p class="text-sm @if(!$salePayment->sale->is_paid) text-error @else text-success @endif">
                <b>Saldo: </b> {{ number_format($salePayment->sale->due_amount, 2) }}
            </p>
        </div>
    </div>
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
            @foreach ($salePayment->sale->payments as $i => $payment)
            <tr class="@if($payment->id == $salePayment->id) bg-orange-200 @endif">
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
</div>