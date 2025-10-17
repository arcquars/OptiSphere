{{-- resources/views/pdf/sale.blade.php --}}
@php
    // ===== Helpers =====
    function m($n) { return number_format((float)($n ?? 0), 2, '.', ','); }

    // Monto total (ajusta a tu campo real si es diferente)
    $total = $sale->total
        ?? $sale->total_amount
        ?? ($sale->items?->sum(fn($i) => ($i->final_price_per_unit ?? $i->price ?? 0) * ($i->quantity ?? 1) - ($i->discount ?? 0)) ?? 0);

    // Descuento total (si tu modelo lo tiene, úsalo; si no, suma por ítem)
    $discountTotal = $sale->discount_total
        ?? $sale->discount
        ?? ($sale->items?->sum('discount') ?? 0);

    // Pagos y saldo (opcional, para mostrar si lo usas)
    $paid = $sale->payments?->sum('amount') ?? 0;
    $balance = $total - $paid;

    // Subtotal (antes de descuento). Ajusta si tienes campo propio.
    $subtotal = ($sale->subtotal ?? ($total + 0));

    // Cliente
    $customerName = $sale->customer->name ?? '—';
    $customerDoc  = $sale->customer->document ?? $sale->customer->nit ?? $sale->customer->ci ?? '—';
    $customerCode = $sale->customer->code ?? $sale->customer_code ?? '—';

    // Empresa / Sucursal
    $branchName   = $sale->branch->name ?? 'CASA MATRIZ';
    $branchPhone  = $sale->branch->phone ?? '—';
    $branchCity   = $sale->branch->city ?? '—';
    $branchAddr   = $sale->branch->address ?? '—';

    // Datos fiscales
    $nitCompany   = $sale->branch->nit ?? $sale->company_nit ?? '—';
    $invoiceNo    = $sale->number ?? $sale->invoice_number ?? $sale->id ?? '—';
    $authCode     = $sale->authorization_code ?? '—';

    // Fecha de emisión
    $issueAt = $sale->date_sale->format('d-m-Y') ?? '—';

    // Palabras (si no llega desde el controlador)
    $amountInWords = $amountInWords
        ?? ($sale->amount_in_words ?? null)
        ?? '—';

    // Unidad por ítem (si la tienes en el ítem o en el producto)
    $unitFor = function($item) {
        return $item->unit
            ?? $item->unit_name
            ?? $item->salable->unit
            ?? $item->salable->unit_name
            ?? 'UNIDAD';
    };

    // Código por ítem (si existe)
    $codeFor = function($item) {
        return $item->code
            ?? $item->salable->code
            ?? $item->salable->sku
            ?? '—';
    };

    // Descripción por ítem
    $descFor = function($item) {
        // Nombre base
        $name = $item->salable->name ?? $item->name ?? '—';
        // Notas/promo extra
        $extra = [];
        if (!empty($item->notes)) $extra[] = $item->notes;
        if (!empty($item->promotion?->name)) $extra[] = 'Promo: ' . $item->promotion->name;

        // Etiqueta de servicio si corresponde
        $isService = false;
        if (property_exists($item, 'is_service')) {
            $isService = (bool) $item->is_service;
        } elseif (isset($item->salable)) {
            $isService = $item->salable instanceof \App\Models\Service;
        }
        if ($isService) $extra[] = '(SERVICIOS)';

        return trim($name . (count($extra) ? ' ' . implode(' — ', $extra) : ''));
    };

    // Precio unitario final
    $unitPriceFor = fn($item) => (float) ($item->final_price_per_unit ?? $item->price ?? 0);

    // Descuento por ítem
    $discountFor  = fn($item) => (float) ($item->discount ?? 0);

    // Subtotal por ítem
    $lineTotalFor = function($item) use ($unitPriceFor, $discountFor) {
        $q = (float) ($item->quantity ?? 1);
        return ($unitPriceFor($item) * $q) - $discountFor($item);
    };
@endphp

    <!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo #{{ $invoiceNo }}</title>
    <style>
        /* ===== Reset & Base ===== */
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111;
            font-size: 11px; /* compacto para media carta */
            line-height: 1.25;
            padding: 14px 18px; /* margencitos internos */
        }

        h1,h2,h3,h4,p { margin: 0; }
        .muted { color:#444; }
        .right { text-align:right; }
        .center { text-align:center; }
        .mt-4 { margin-top: 12px; }
        .mt-6 { margin-top: 16px; }
        .mb-2 { margin-bottom: 6px; }
        .mb-4 { margin-bottom: 12px; }
        .hr { border-bottom: 1px solid #999; margin: 6px 0; }

        /* ===== Layout ===== */
        .row { width: 100%; display: table; table-layout: fixed; }
        .col { display: table-cell; vertical-align: top; }
        .col-6 { width: 50%; }
        .col-7 { width: 58%; }
        .col-5 { width: 42%; }

        /* ===== Tables ===== */
        table { width:100%; border-collapse: collapse; }
        th, td { padding: 6px; border: 1px solid #DADADA; vertical-align: top; }
        th { background: #F2F2F2; font-weight: bold; }

        .no-border, .no-border td { border: none !important; padding: 2px 0; }

        /* ===== Header ===== */
        .header {
            margin-bottom: 6px;
        }
        .title {
            font-size: 16px;
            font-weight: 700;
        }
        .subtitle {
            font-size: 12px;
            font-weight: 600;
        }
        .box {
            border: 1px solid #DADADA;
            padding: 8px;
            border-radius: 2px;
        }
        .label { font-weight: 600; }

        /* ===== Totals ===== */
        .totals td { border: none; padding: 4px 0; }
        .totals .label { text-align: right; padding-right: 6px; }
        .totals .value { text-align: right; width: 120px; }

        /* ===== Legal ===== */
        .legal { font-size: 10px; color: #333; line-height: 1.3; }

        .p-base {
            font-size: 10px;
        }

        .cesi-table thead tr th {
            font-size: 10px;
        }
    </style>
</head>
<body>

{{-- ENCABEZADO IZQ: Empresa/Sucursal  ||  DER: FACTURA y Autorización --}}
<div class="row header">
    <div class="col col-6" style="text-align: center">
        <img src="{{ public_path('img/android-chrome-192x192.png') }}" alt="Logo" style="max-width: 60px; height: auto;">
        <div class="title" style="color: red;">{{ config('cerisier.company_name') }}</div>
        <div class="subtitle">{{ strtoupper($branchName) }}</div>
    </div>
    <div class="col col-6">
        <div class="">
            <div class="row">
                <div class="col">
                    <div class="subtitle center" style="font-size: 32px">RECIBO</div>
                    <div class="right" style="margin-top:6px; font-size: 12px">
                        <span class="label">VENTA NRO.</span> <strong>{{ $invoiceNo }}</strong>
                    </div>
                    <div style="text-align: right; font-size: 12px">
                        <span class="label">Fecha:</span>
                        {{ $issueAt }}
                    </div>
                    <div style="text-align: right; font-size: 12px">
                        <span class="label">Usuario:</span>
                        {{ $sale->user->name }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>
{{-- DATOS DEL CLIENTE --}}
<div class="mb-4">
    <table class="no-border">
        <tr>
            <td class="no-border" style="font-size: 12px;"><span class="label">Nombre:</span> {{ $customerName }}</td>
        </tr>
    </table>
</div>

{{-- DETALLE DE ÍTEMS --}}
<table class="cesi-table">
    <thead>
    <tr>
        <th style="width: 13%;">CÓDIGO</th>
        <th style="width: 13%;">CANTIDAD</th>
        <th style="width: 12%;">U. MEDIDA</th>
        <th>DESCRIPCIÓN</th>
        <th style="width: 13%;" class="right">P. UNITARIO</th>
        <th style="width: 12%;" class="right">DESCUENTO</th>
        <th style="width: 13%;" class="right">SUBTOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($sale->items ?? [] as $item)
        @php
            $qty   = (float) ($item->quantity ?? 1);
            $unit  = $unitFor($item);
            $code  = $codeFor($item);
            $desc  = $descFor($item);
            $punit = $unitPriceFor($item);
            $disc  = $discountFor($item);
            $line  = $lineTotalFor($item);
        @endphp
        <tr>
            <td>{{ $code }}</td>
            <td class="center">{{ m($qty) }}</td>
            <td class="center">{{ strtoupper($unit) }}</td>
            <td>{!! e($desc) !!}</td>
            <td class="right">{{ m($punit) }}</td>
            <td class="right">{{ m($disc) }}</td>
            <td class="right">{{ m($line) }}</td>
        </tr>

        @if(count($item->attachedServices) > 0)
            @foreach($item->attachedServices as $attached)
                <tr>
                    <td>
                        {{ $attached->service->code ?? '—' }}
                    </td>
                    <td class="center">{{ $attached->quantity }}</td>
                    <td class="center">
                        SERVICIO
                    </td>
                    <td>
                        {{ $descFor($attached->service) }}
{{--                        @if($attached->promotion_id)--}}
{{--                            {{ number_format($attached->price_per_unit - ($attached->price_per_unit * $attached->promotion_discount_rate / 100), 2) }} <small>({{ $attached->price_per_unit }} - {{ $attached->promotion_discount_rate }}%)</small>--}}
{{--                        @else--}}
{{--                            {{ $attached->price_per_unit }}--}}
{{--                        @endif--}}
                    </td>
                    <td class="right">
                        {{ $attached->price_per_unit }}
{{--                        {{ config('cerisier.currency_symbol') }} {{ $attached->subtotal }}--}}
                    </td>
                    <td class="right">
                        {{ $attached->promotion_discount_rate }}
                    </td>
                    <td class="right">
                        {{ $attached->subtotal }}
                    </td>
                </tr>
            @endforeach
        @endif
    @endforeach
    </tbody>
</table>

{{-- TOTALES Y LEYENDAS --}}
<div class="row mt-6">
    <div class="col col-7">
        <table class="no-border">
            <tr>
                <td class="no-border">
                    <span class="label">Son:</span>
                    {{ $amountInWords !== '—' ? $amountInWords : '—' }}
                    <span class="label">BOLIVIANOS</span>
                </td>
            </tr>
            <tr>
                <td class="no-border">
                    <span class="label">Total productos:</span> {{ $sale->amount_products }}
                </td>
            </tr>
            <tr>
                <td class="no-border">
                    <span class="label">Total servicios:</span> {{ $sale->amount_services }}
                </td>
            </tr>
        </table>
    </div>
    <div class="col col-5">
        <table class="totals" style="width:100%;">
            <tr>
                <td class="label"><strong>SUBTOTAL</strong></td>
                <td class="value">{{ m($subtotal) }}</td>
            </tr>
            <tr>
                <td class="label"><strong>DESCUENTO</strong></td>
                <td class="value">{{ m($discountTotal) }}</td>
            </tr>
            <tr>
                <td class="label"><strong>TOTAL</strong></td>
                <td class="value">{{ m($total) }}</td>
            </tr>
            <tr>
                <td class="label"><strong>MONTO A PAGAR</strong></td>
                <td class="value"><strong>{{ m($total) }}</strong></td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>

