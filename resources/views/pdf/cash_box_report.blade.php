<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte Cierre de Caja #{{ $closing->id }}</title>
    <style>
        /* Estilos CSS aquí, DomPDF prefiere CSS en línea o en <style> */
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .totals-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .totals-table th, .totals-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .totals-table th { background-color: #f2f2f2; }
        .total-row { font-weight: bold; background-color: #e0e0e0; }

        /* Estilos para la nueva tabla de información del encabezado */
        .header-info-table {
            width: 80%; /* Ajusta el ancho según necesites */
            margin: 10px auto; /* Centrar la tabla */
            border-collapse: collapse;
            font-size: 11px;
        }
        .header-info-table td {
            padding: 4px 10px;
        }
        .header-info-table .label {
            font-weight: bold;
            width: 20%; /* Ancho de la columna de etiquetas */
        }
        .header-info-table .value {
            width: 30%; /* Ancho de la columna de valores */
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Reporte de Cierre de Caja</h1>
    <table class="header-info-table">
        <tbody>
        <tr>
            <td class="label">Sucursal:</td>
            <td class="value">{{ $branchName }}</td>
            <td class="label">Usuario:</td>
            <td class="value">{{ $userName }}</td>
        </tr>
        <tr>
            <td class="label">Desde:</td>
            <td class="value">{{ $closing->opening_time }}</td>
            <td class="label">Hasta:</td>
            <td class="value">{{ $closing->closing_time }}</td>
        </tr>
        </tbody>
    </table>
</div>

<table class="totals-table">
    <thead>
    <tr>
        <th>Concepto</th>
        <th style="text-align: right;">Monto</th>
    </tr>
    </thead>
    <tbody>
    <tr><td>Balance Inicial</td><td style="text-align: right;">{{ number_format($closing->initial_balance, 2) }}</td></tr>
    <tr><td>Ventas Contado (Efectivo)</td><td style="text-align: right;">{{ number_format($totals['sales']['cash'], 2) }}</td></tr>
    <tr><td>Cobros Crédito (Efectivo)</td><td style="text-align: right;">{{ number_format($totals['credit_payments']['cash'], 2) }}</td></tr>
    <tr><td>Ingresos por Movimientos</td><td style="text-align: right;">{{ number_format($totals['movements']['incomes'], 2) }}</td></tr>
    <tr><td>Egresos por Movimientos</td><td style="text-align: right;">-{{ number_format($totals['movements']['expenses'], 2) }}</td></tr>
    <tr class="total-row">
        <td>TOTAL EN SISTEMA</td>
        <td style="text-align: right;">{{ number_format($totals['system_total'], 2) }}</td>
    </tr>
    <tr class="total-row">
        <td>BALANCE REAL (CAJERO)</td>
        <td style="text-align: right;">{{ number_format($closing->actual_balance, 2) }}</td>
    </tr>
    <tr class="total-row">
        <td>DIFERENCIA</td>
        <td style="text-align: right;">{{ number_format($closing->difference, 2) }}</td>
    </tr>
    </tbody>
</table>

<p style="margin-top: 20px;">Notas: {{ $closing->notes }}</p>
</body>
</html>
