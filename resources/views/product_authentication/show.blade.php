<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Certificado de Autenticidad — {{ config('cerisier.company_name') }}</title>
    @vite(['resources/css/app.css'])
</head>

<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">
    <div class="card bg-base-100 shadow-xl w-full max-w-2xl">
        <div class="card-body">
            <div class="text-center">
                <div class="badge badge-success badge-lg gap-2 py-4">
                    ¡Producto Auténtenico Verificado!
                </div>
                <h1 class="card-title justify-center mt-4 text-2xl">
                    {{ $auth->product?->name ?? 'Producto no disponible' }}
                </h1>
                @if ($auth->product?->code)
                    <p class="text-base-content/60">Código: {{ $auth->product->code }}</p>
                @endif
            </div>

            <div class="divider"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-base-content/60">Folio</p>
                    <p class="font-semibold">#{{ $auth->id }}</p>
                </div>
                <div>
                    <p class="text-sm text-base-content/60">Fecha de compra</p>
                    <p class="font-semibold">{{ $auth->fecha_compra?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div class="">
                    <p class="text-sm text-base-content/60">Cliente</p>
                    <p class="font-semibold">{{ $auth->cliente }}</p>
                </div>
                <div class="">
                    <p class="text-sm text-base-content/60">Optica</p>
                    <p class="font-semibold">{{ $auth->frequentCustomer?->user?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-base-content/60">Autenticado el</p>
                    <p class="font-semibold">
                        {{ $auth->authentication_approved_date?->format('d/m/Y H:i') ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-base-content/60">Autenticado por</p>
                    <p class="font-semibold">{{ $auth->authentication_approved_by ?? '—' }}</p>
                </div>
            </div>

            <div class="divider"></div>

            <p class="text-center text-sm text-base-content/60">
                Este certificado confirma que el producto fue verificado por
                {{ config('cerisier.company_name') }}.
            </p>
        </div>
    </div>
</body>

</html>
