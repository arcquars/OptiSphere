{{-- resources/views/reports/sales-report-pdf.blade.php --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        /* * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        } */

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1f2937;
        }

        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
        }

        .header p {
            font-size: 9px;
            color: #6b7280;
            margin-top: 4px;
        }

        .filters {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 8px;
            color: #374151;
        }

        .filters strong {
            color: #1f2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        thead tr {
            background-color: #2563eb;
            color: #ffffff;
        }

        thead th {
            padding: 6px 5px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
        }

        thead th.text-right {
            text-align: right;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tbody td {
            padding: 5px 5px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8px;
            vertical-align: middle;
        }

        tbody td.text-right {
            text-align: right;
            font-weight: bold;
            font-family: DejaVu Sans Mono, monospace;
        }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .badge-credit  { background: #fef3c7; color: #92400e; }
        .badge-paid    { background: #d1fae5; color: #065f46; }
        .badge-promo   { background: #d1fae5; color: #065f46; }
        .badge-nopromo { background: #fee2e2; color: #991b1b; }

        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            font-size: 8px;
            color: #9ca3af;
            text-align: right;
        }

        .total-row {
            background-color: #eff6ff !important;
            font-weight: bold;
        }

        .total-row td {
            border-top: 2px solid #2563eb;
            font-size: 9px;
        }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="header">
        <h1>Reporte de Ventas</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- Filtros aplicados --}}
    <div class="filters">
        <strong>Filtros aplicados:</strong>
        Período: {{ \Carbon\Carbon::parse($dateStart)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateEnd)->format('d/m/Y') }}
        @if($branchName) &nbsp;|&nbsp; Sucursal: <strong>{{ $branchName }}</strong> @endif
        @if($statusLabel) &nbsp;|&nbsp; Estado: <strong>{{ $statusLabel }}</strong> @endif
        @if($typeSaleLabel) &nbsp;|&nbsp; Tipo: <strong>{{ $typeSaleLabel }}</strong> @endif
        @if($isFacturado) &nbsp;|&nbsp; <strong>Solo facturadas</strong> @endif
    </div>

    {{-- Tabla --}}
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>NIT</th>
                <th>Sucursal</th>
                <th>Tipo</th>
                <th>Pago</th>
                <th>Estado</th>
                <th>Promo</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->date_sale->format('d/m/Y') }}</td>
                    <td>{{ $sale->customer->name ?? '-' }}</td>
                    <td>{{ $sale->customer->nit ?? '-' }}</td>
                    <td>{{ $sale->branch->name ?? '-' }}</td>
                    <td>{{ strtoupper($sale->sale_type) }}</td>
                    <td>{{ $sale->payment_method }}</td>
                    <td>
                        @if($sale->status === \App\Models\Sale::SALE_STATUS_CREDIT)
                            <span class="badge badge-credit">
                                {{ __('cerisier.' . $sale->status) }}
                            </span>
                        @else
                            <span class="badge badge-paid">
                                {{ __('cerisier.' . $sale->status) }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($sale->use_promotion)
                            <span class="badge badge-promo">Sí</span>
                        @else
                            <span class="badge badge-nopromo">No</span>
                        @endif
                    </td>
                    <td class="text-right">{{ config('cerisier.currency_symbol') }} {{ number_format($sale->final_total, 2) }}</td>
                </tr>
            @endforeach

            {{-- Fila de total --}}
            <tr class="total-row">
                <td colspan="9" style="text-align: right; padding-right: 8px;">
                    TOTAL ({{ $sales->count() }} ventas):
                </td>
                <td class="text-right">
                    {{ config('cerisier.currency_symbol') }} {{ number_format($sales->sum('final_total'), 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Página <span class="pagenum"></span>
        &nbsp;|&nbsp;
        Total de registros: {{ $sales->count() }}
    </div>

</body>
</html>