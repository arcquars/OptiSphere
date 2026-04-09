<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Recibo de Movimiento de Caja #{{ $cashMovement->id }}</title>
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

        /* ── Badge de tipo ────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .badge-ingresos {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .badge-gastos {
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

        /* ── Recuadro de monto principal ──────────────── */
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
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .amount-value {
            font-size: 28px;
            font-weight: bold;
            color: #ffffff;
        }

        .amount-currency {
            font-size: 14px;
            color: #9ca3af;
            margin-left: 4px;
        }

        .amount-type-inner {
            padding: 14px 20px;
            text-align: center;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .amount-type-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        /* ── Descripción ─────────────────────────────── */
        .desc-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 11px;
            color: #374151;
            margin-bottom: 14px;
            min-height: 36px;
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

        .td-right {
            text-align: right;
        }

        /* ── Línea divisora ──────────────────────────── */
        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 12px 0;
        }

        .divider-strong {
            border: none;
            border-top: 2px solid #6A6C75;
            margin: 12px 0;
        }

        /* ── Footer ──────────────────────────────────── */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .footer-table {
            width: 100%;
        }

        .footer-table td {
            vertical-align: bottom;
            font-size: 9px;
            color: #9ca3af;
        }

        .signature-line {
            border-top: 1px solid #374151;
            width: 160px;
            margin: 24px auto 4px auto;
        }

        .signature-label {
            text-align: center;
            font-size: 9px;
            color: #6b7280;
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
                    <div class="brand">{{ config('app.name') }}</div>
                    <div class="brand-sub">Sistema de Gestión</div>
                </td>
                <td style="width:40%;" class="doc-title">
                    <div class="title-text">Recibo de Caja</div>
                    <div class="doc-number"># {{ str_pad($cashMovement->id, 6, '0', STR_PAD_LEFT) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── MONTO DESTACADO ──────────────────────────────────── --}}
    <table class="amount-box" style="width:100%; margin-bottom:16px;">
        <tr>
            <td style="width:55%; padding-right:8px;">
                <div class="amount-inner">
                    <div class="amount-label">Monto del movimiento</div>
                    <div class="amount-value">
                        {{ number_format($cashMovement->amount, 2) }}
                        <span class="amount-currency">Bs.</span>
                    </div>
                </div>
            </td>
            <td style="width:45%; padding-left:8px;">
                <div class="amount-type-inner">
                    <div class="amount-type-label">Tipo de movimiento</div>
                    @if(strtoupper($cashMovement->type) === 'INCOME')
                        <span class="badge badge-ingresos">&#8593; INGRESO</span>
                    @else
                        <span class="badge badge-gastos">&#8595; GASTO</span>
                    @endif
                    <div style="font-size:9px; color:#6b7280; margin-top:6px;">
                        {{ strtoupper($cashMovement->type) }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ── INFORMACIÓN GENERAL ──────────────────────────────── --}}
    <div class="section">
        <div class="section-title">Información del Movimiento</div>
        <table class="info-table">
            <tr>
                <td>
                    <div class="label">Sucursal</div>
                    <div class="value font-bold">
                        {{ $cashMovement->branch->name ?? $cashMovement->branch_id }}
                    </div>
                </td>
                <td>
                    <div class="label">Caja Cerrada N°</div>
                    <div class="value">{{ $cashMovement->cashBoxClosing->isClosing? 'Cerrado' : 'Abierto' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Registrado por</div>
                    <div class="value">
                        {{ $cashMovement->user->name ?? '—' }}
                    </div>
                </td>
                <td>
                    <div class="label">Fecha de creación</div>
                    <div class="value">
                        {{ \Carbon\Carbon::parse($cashMovement->created_at)->format('d/m/Y H:i:s') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── DESCRIPCIÓN ──────────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">Descripción / Concepto</div>
        <div class="desc-box">
            {{ $cashMovement->description ?? 'Sin descripción' }}
        </div>
    </div>

    {{-- ── LÍNEA DE FIRMA ───────────────────────────────────── --}}
    <div style="margin-top:30px;">
        <table style="width:100%;">
            <tr>
                <td style="width:50%; text-align:center;">
                    <div class="signature-line"></div>
                    <div class="signature-label">Firma del Responsable</div>
                    <div style="font-size:9px; color:#9ca3af; margin-top:2px;">
                        {{ $cashMovement->user->name ?? '' }}
                    </div>
                </td>
                <td style="width:50%; text-align:center;">
                    <div class="signature-line"></div>
                    <div class="signature-label">Sello / Visto Bueno</div>
                    <div style="font-size:9px; color:#9ca3af; margin-top:2px;">
                        {{ $cashMovement->branch->name ?? '' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>