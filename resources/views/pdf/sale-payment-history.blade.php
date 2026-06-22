<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Historial de Abonos - Venta #{{ $sale->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #ffffff;
            padding: 24px 28px;
        }

        /* ── Cabecera ─────────────────────────────────── */
        .header {
            width: 100%;
            border-bottom: 3px solid #1a1a2e;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .header-top {
            width: 100%;
        }

        .header-top td {
            vertical-align: middle;
        }

        .brand {
            font-size: 20px;
            font-weight: bold;
            color: #1a1a2e;
            letter-spacing: 1px;
        }

        .brand-sub {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title .title-text {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a2e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title .doc-number {
            font-size: 11px;
            color: #6b7280;
            margin-top: 3px;
        }

        /* ── Alerta QR activo ──────────────────────────── */
        .alert-box {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            color: #92400e;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 14px;
        }

        /* ── Badge de estado ───────────────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .badge-pagado {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .badge-pendiente {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* ── Sección de datos ─────────────────────────── */
        .section {
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }

        /* ── Tabla de datos generales ─────────────────── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px 8px;
            vertical-align: top;
            width: 50%;
        }

        .info-table tr:nth-child(odd) td {
            background-color: #f9fafb;
        }

        .info-table tr:nth-child(even) td {
            background-color: #ffffff;
        }

        .label {
            font-size: 9px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .value {
            font-size: 11px;
            color: #111827;
            margin-top: 1px;
        }

        /* ── Recuadro de saldo ─────────────────────────── */
        .amount-box {
            width: 100%;
            margin-bottom: 16px;
        }

        .amount-box td {
            vertical-align: middle;
        }

        .amount-inner {
            background-color: #6A6C75;
            border-radius: 4px;
            padding: 14px 20px;
            text-align: center;
        }

        .amount-label {
            font-size: 9px;
            color: #d1d5db;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .amount-value {
            font-size: 22px;
            font-weight: bold;
            color: #ffffff;
        }

        .amount-currency {
            font-size: 12px;
            color: #d1d5db;
            margin-left: 4px;
        }

        /* ── Tabla de detalles ────────────────────────── */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .detail-table th {
            background-color: #6A6C75;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 10px;
            text-align: left;
        }

        .detail-table td {
            padding: 7px 10px;
            font-size: 11px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
        }

        .detail-table tr:last-child td {
            border-bottom: none;
        }

        .detail-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        .detail-table tr.is-last-payment td {
            background-color: #fef3c7;
        }

        .td-right {
            text-align: right;
        }

        .td-center {
            text-align: center;
        }

        /* ── Footer ──────────────────────────────────── */
        .footer {
            margin-top: 24px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .signature-line {
            border-top: 1px solid #374151;
            width: 160px;
            margin: 30px auto 4px auto;
        }

        .signature-label {
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }

        .print-meta {
            margin-top: 14px;
            font-size: 8px;
            color: #9ca3af;
            text-align: right;
        }

        /* ── Utilidades ──────────────────────────────── */
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .font-bold   { font-weight: bold; }
        .text-green  { color: #166534; }
        .text-red    { color: #991b1b; }
        .text-gray   { color: #6b7280; }
    </style>
</head>
<body>

    {{-- ── CABECERA ─────────────────────────────────────────── --}}
    <div class="header">
        <table class="header-top" style="width:100%;">
            <tr>
                <td style="width:60%;">
                    <div class="brand">{{ $sale->branch->name ?? config('app.name') }}</div>
                    <div class="brand-sub">Historial y Registro de Abonos</div>
                </td>
                <td style="width:40%;" class="doc-title">
                    <div class="title-text">Venta a Crédito</div>
                    <div class="doc-number"># {{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── ALERTA QR ACTIVO ─────────────────────────────────── --}}
    @if($isQrActive)
    <div class="alert-box">
        Existe un QR de pago activo para esta venta a crédito
    </div>
    @endif

    {{-- ── INFORMACIÓN GENERAL ──────────────────────────────── --}}
    <div class="section">
        <div class="section-title">Información de la Venta</div>
        <table class="info-table">
            <tr>
                <td>
                    <div class="label">Fecha de venta</div>
                    <div class="value font-bold">
                        {{ optional($sale->date_sale)->format('d/m/Y') ?? '—' }}
                    </div>
                </td>
                <td>
                    <div class="label">Cliente</div>
                    <div class="value">{{ $sale->customer->name ?? '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Vendedor</div>
                    <div class="value">{{ $sale->user->name ?? '—' }}</div>
                </td>
                <td>
                    <div class="label">Sucursal</div>
                    <div class="value">{{ $sale->branch->name ?? '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Monto total de venta</div>
                    <div class="value font-bold">{{ number_format($sale->final_total, 2) }}</div>
                </td>
                <td>
                    <div class="label">Estado</div>
                    <div class="value">
                        @if($sale->is_paid)
                            <span class="badge badge-pagado">Pagado</span>
                        @else
                            <span class="badge badge-pendiente">Con saldo</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── SALDO PENDIENTE ──────────────────────────────────── --}}
    <table class="amount-box" style="width:100%; margin-bottom:16px;">
        <tr>
            <td>
                <div class="amount-inner">
                    <div class="amount-label">Saldo pendiente</div>
                    <div class="amount-value">
                        {{ number_format($sale->final_total - $sale->paid_amount, 2) }}
                        <span class="amount-currency">{{ config('cerisier.currency_symbol', 'Bs.') }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ── DETALLE DE ABONOS ────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">Detalle de Abonos</div>

        @if($payments->isNotEmpty())
        <table class="detail-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Fecha</th>
                    <th>Método</th>
                    <th class="td-right">Monto</th>
                    <th class="td-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $i => $payment)
                <tr class="@if($sale->lastPayment && $payment->id == $sale->lastPayment->id) is-last-payment @endif">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $payment->user->name ?? '—' }}</td>
                    <td>{{ optional($payment->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td class="td-right">{{ number_format($payment->amount, 2) }}</td>
                    <td class="td-right">{{ number_format($payment->residue, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-center text-gray" style="padding: 14px 0;">Sin registro de pagos</p>
        @endif
    </div>

    {{-- ── LÍNEA DE FIRMA ───────────────────────────────────── --}}
    <div class="footer">
        <table style="width:100%;">
            <tr>
                <td style="width:50%; text-align:center;">
                    <div class="signature-line"></div>
                    <div class="signature-label">Entregado por</div>
                </td>
                <td style="width:50%; text-align:center;">
                    <div class="signature-line"></div>
                    <div class="signature-label">Recibido por (Cliente)</div>
                    <div style="font-size:9px; color:#9ca3af; margin-top:2px;">
                        {{ $sale->customer->name ?? '' }}
                    </div>
                </td>
            </tr>
        </table>
        <div class="print-meta">
            Generado el {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>
