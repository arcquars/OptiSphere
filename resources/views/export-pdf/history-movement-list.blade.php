<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles del Movimiento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: -10px;
        }

        /* Contenedor Superior (Cabecera) */
        .header-card {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            position: relative;
        }

        .title {
            color: #9ca3af;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 8px 0;
            letter-spacing: 1px;
        }

        .user-text {
            font-size: 12px;
            color: #1f2937;
            margin-bottom: 12px;
        }

        table.badges-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px 0;
            margin-left: -5px;
        }
        table.badges-table td {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: bold;
            white-space: nowrap;
        }

        .b-almacen { background-color: #f3f0ff; color: #6d28d9; border: 1px solid #e9d5ff; }
        .b-fecha { background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .b-accion-success { background-color: #10b981; color: #ffffff; border: 1px solid #059669; }
        .b-accion-info { background-color: #00BAFE; color: #000; border: 1px solid #00BAFA; }
        .b-accion-warning { background-color: #FFB901; color: #000; border: 1px solid #FFB903; }

        /* Tabla de movimientos */
        table.movements {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        table.movements th {
            background-color: #1f2937;
            color: #ffffff;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        table.movements td {
            border-bottom: 1px solid #e5e7eb;
            padding: 6px 8px;
        }

        table.movements tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        .text-end { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <!-- Tarjeta de Cabecera -->
    <div class="header-card">
        <h2 class="title">Detalle de movimiento</h2>
        <div class="user-text">
            <strong>Registrado por:</strong> {{ $registrado_por }}
        </div>

        <table class="badges-table">
            <tr>
                <td class="b-accion-{{ $bgAction }}">Acción : {{ $accion }} # {{ $warehouseM->id }}</td>
                <td class="b-almacen">Almacén: {{ $warehouseM->warehouse->name ?? 'N/A' }}</td>
                @if(strcmp($accion, "ENTREGA") == 0)
                <td class="b-almacen">Se entregó a: {{ $warehouseM->branch->name ?? 'N/A' }}</td>
                @elseif(strcmp($accion, "DEVOLUCION") == 0)
                <td class="b-almacen">Devolvió: {{ $warehouseM->branch->name ?? 'N/A' }}</td>
                @endif
                <td class="b-fecha">Fecha : {{ $fecha }}</td>
                <td style="width: 100%;"></td>
            </tr>
        </table>
    </div>

    <!-- Tabla de Movimientos -->
    <table class="movements">
        <thead>
            <tr>
                <th>ID</th>
                <th>Producto</th>
                <th class="text-end">Cantidad Anterior</th>
                <th class="text-end">Diferencia</th>
                <th class="text-end">Cantidad Nueva</th>
            </tr>
        </thead>
        <tbody>
            @forelse($warehouseStockHistories as $whsh)
                <tr>
                    <td>{{ $whsh->id }}</td>
                    <td>{{ $whsh->warehouseStock->product->name ?? 'N/A' }}</td>
                    <td class="text-end">{{ number_format($whsh->old_quantity) }}</td>
                    <td class="text-end">{{ number_format($whsh->difference) }}</td>
                    <td class="text-end">{{ number_format($whsh->new_quantity) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No hay movimientos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>