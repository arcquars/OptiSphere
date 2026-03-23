<div>
    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100">
        <table class="table">
            <!-- head -->
            <thead>
            <tr>
                <th></th>
                <th>Fecha Venta</th>
                <th>Total</th>
                <th>Saldo</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($records as $record)
                <tr>
                    <th>{{ $record->sale_id }}</th>
                    <td>{{ $record->sale->date_sale }}</td>
                    <td>{{ $record->sale->total_amount }}</td>
                    <td>{{ $record->sale->due_amount }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="text-center m-2">
        <h3 class="pl-4 text-success font-bold">Total a PAGAR: {{ $records->sum('sale.due_amount') }}</h3>
    </div>
</div>