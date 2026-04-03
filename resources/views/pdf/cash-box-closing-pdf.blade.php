<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cierre de Caja</title>
    <style>
        /* Reset y base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            background-color: #ffffff;
            padding: 24px;
        }

        /* Cabecera del documento */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #374151;
            padding-bottom: 12px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 11px;
            color: #6b7280;
        }

        /* Badge de estado */
        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .status-closed {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .status-open {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* Grid de información general */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .info-grid td {
            padding: 6px 8px;
            vertical-align: top;
            width: 33.33%;
        }

        .info-grid .label {
            font-size: 10px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-grid .value {
            font-size: 12px;
            color: #111827;
            margin-top: 2px;
        }

        /* Sección con borde */
        .section {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .section-title {
            background-color: #f3f4f6;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tabla de totales por sección */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table tr td {
            padding: 5px 10px;
            font-size: 11px;
            border-bottom: 1px solid #f3f4f6;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .totals-table .td-label {
            color: #4b5563;
        }

        .totals-table .td-value {
            text-align: right;
            font-weight: 600;
            color: #111827;
        }

        /* Columnas de 3 bloques */
        .three-col-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 16px;
        }

        .three-col-table td {
            width: 33.33%;
            vertical-align: top;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        /* Balance row con color */
        .balance-row td {
            padding: 6px 10px;
            font-size: 11px;
            font-weight: bold;
            border-top: 1px solid #e5e7eb;
        }

        /* Colores semánticos */
        .text-success { color: #16a34a; }
        .text-danger  { color: #dc2626; }
        .text-muted   { color: #6b7280; }

        /* Diferencia */
        .diff-positive { color: #16a34a; font-weight: bold; }
        .diff-negative { color: #dc2626; font-weight: bold; }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 4px 0;
        }

        /* Nota */
        .note-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 11px;
            color: #92400e;
        }

        .note-box .note-label {
            font-weight: bold;
            margin-bottom: 3px;
        }

        /* Fila de resumen general */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .summary-table td {
            padding: 6px 10px;
            font-size: 11px;
            border: 1px solid #e5e7eb;
        }

        .summary-table .summary-label {
            background-color: #f9fafb;
            font-weight: bold;
            color: #374151;
            width: 40%;
        }

        /* Footer */
        .footer {
            margin-top: 24px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

    {{-- Cabecera --}}
    <div class="header">
        <h1>Reporte de Cierre de Caja</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    {{-- Estado + Info general --}}
    <table class="info-grid">
        <tr>
            <td>
                <div class="label">Estado</div>
                <div class="value" style="margin-top: 4px;">
                    @if(strcmp($record->status, 'closed') == 0)
                        <span class="status-badge status-closed">CERRADO</span>
                    @else
                        <span class="status-badge status-open">ABIERTO</span>
                    @endif
                </div>
            </td>
            <td>
                <div class="label">Sucursal</div>
                <div class="value">{{ $record->branch->name }}</div>
            </td>
            <td>
                <div class="label">Usuario</div>
                <div class="value">{{ $record->user->name }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Fecha de apertura</div>
                <div class="value">{{ $record->opening_time }}</div>
            </td>
            <td>
                <div class="label">Fecha de cierre</div>
                <div class="value">{{ $record->closing_time ?? '—' }}</div>
            </td>
            <td>
                <div class="label">Balance Inicial</div>
                <div class="value">{{ number_format($record->initial_balance, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Resumen de balances --}}
    <table class="summary-table">
        <tr>
            <td class="summary-label">Balance Sistema (Esperado)</td>
            <td>{{ number_format($record->expected_balance, 2) }}</td>
            <td class="summary-label">Balance Actual</td>
            <td>{{ number_format($record->actual_balance, 2) }}</td>
            <td class="summary-label">Diferencia</td>
            <td class="{{ $record->difference >= 0 ? 'diff-positive' : 'diff-negative' }}">
                {{ number_format($record->difference, 2) }}
            </td>
        </tr>
    </table>

    {{-- Nota (solo si está cerrado) --}}
    @if(strcmp($record->status, 'closed') == 0 && $record->notes)
    <div class="note-box">
        <div class="note-label">Nota de cierre</div>
        <div>{{ $record->notes }}</div>
    </div>
    @endif

    {{-- Bloques de totales --}}
    <table class="three-col-table">
        <tr>
            {{-- Ventas al contado --}}
            <td>
                <div class="section-title">Ventas al Contado</div>
                <table class="totals-table">
                    <tr>
                        <td class="td-label">Efectivo</td>
                        <td class="td-value">{{ number_format($totals['sales']['cash'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="td-label">Transferencia</td>
                        <td class="td-value">{{ number_format($totals['sales']['transfer'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="td-label">QR</td>
                        <td class="td-value">{{ number_format($totals['sales']['qr'], 2) }}</td>
                    </tr>
                </table>
            </td>

            {{-- Cobros de crédito --}}
            <td>
                <div class="section-title">Cobros de Crédito</div>
                <table class="totals-table">
                    <tr>
                        <td class="td-label">Efectivo</td>
                        <td class="td-value">{{ number_format($totals['credit_payments']['cash'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="td-label">Transferencia</td>
                        <td class="td-value">{{ number_format($totals['credit_payments']['transfer'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="td-label">QR</td>
                        <td class="td-value">{{ number_format($totals['credit_payments']['qr'], 2) }}</td>
                    </tr>
                </table>
            </td>

            {{-- Movimientos --}}
            <td>
                <div class="section-title">Movimientos</div>
                <table class="totals-table">
                    <tr>
                        <td class="td-label">Ingresos</td>
                        <td class="td-value text-success">{{ number_format($totals['movements']['incomes'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="td-label">Egresos</td>
                        <td class="td-value text-danger">{{ number_format($totals['movements']['expenses'], 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr class="divider"></td>
                    </tr>
                    <tr>
                        <td class="td-label" style="font-weight:bold;">Balance inicial</td>
                        <td class="td-value text-success">{{ number_format($record->initial_balance, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="td-label" style="font-weight:bold;">Total sistema</td>
                        <td class="td-value">{{ number_format($totals['system_total'], 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Documento generado automáticamente &mdash; {{ config('app.name') }}
    </div>

</body>
</html>