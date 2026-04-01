<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles de la Consulta</title>
    <style>
        /* Configuraciones Base para DomPDF */
        body {
            font-family: Arial, sans-serif;
            margin: -10px; /* Reducir márgenes por defecto */
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

        /* Diseño de Badges usando tabla para alinear en PDF */
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

        /* Colores de los Badges */
        .b-almacen { background-color: #f3f0ff; color: #6d28d9; border: 1px solid #e9d5ff; }
        .b-tipo { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .b-codigo { background-color: #ecfeff; color: #0891b2; border: 1px solid #a5f3fc; }
        .b-fecha { background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .b-accion-success { background-color: #10b981; color: #ffffff; border: 1px solid #059669; }
        .b-accion-info { background-color: #00BAFE; color: #000; border: 1px solid #00BAFA; }
        .b-accion-warning { background-color: #FFB901; color: #000; border: 1px solid #FFB903; }
        

        /* Matriz (Tabla Principal) */
        table.matrix {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            text-align: center;
        }
        
        table.matrix th, table.matrix td {
            border: 1px solid #cbd5e1;
            padding: 4px 0;
            width: 3.8%; /* Para repartir 26 columnas */
        }

        /* Cabeceras de la Matriz */
        table.matrix th.col-header { background-color: #00d084; color: #ffffff; }
        table.matrix th.row-header { background-color: #ef4444; color: #ffffff; }

        /* Bordes divisorios para formar la cuadrícula principal */
        .border-right-thick { border-right: 2px solid #64748b !important; }
        .border-bottom-thick { border-bottom: 2px solid #64748b !important; }

        /* Colores de los valores */
        .val-plus { color: #3b82f6; font-weight: bold; font-size: 11px;} /* Azul */
        .val-num { color: #eab308; font-weight: bold; font-size: 10px;}  /* Amarillo */
    </style>
</head>
<body>

    <!-- Tarjeta de Cabecera -->
    <div class="header-card">
        <h2 class="title">Detalle de movimiento</h2>
        <div class="user-text">
            <strong>Registrado por:</strong> {{ $registrado_por }}
        </div>

        <!-- Badges -->
        <table class="badges-table">
            <tr>
                {{-- bgAction --}}
                <td class="b-accion-{{ $bgAction }}">Acción : {{ $accion }}</td>
                <td class="b-almacen">Almacén: {{ $warehouseM->warehouse->name }}</td>
                @if(strcmp($accion, "ENTREGA") == 0)
                <td class="b-almacen">Se entrego a: {{ $warehouseM->branch->name }}</td>
                @elseif(strcmp($accion, "DEVOLUCION") == 0)
                <td class="b-almacen">Devolvio: {{ $warehouseM->branch->name }}</td>
                @endif
                <td class="b-tipo">Tipo: {{ $type }}</td>
                <td class="b-codigo">Código: {{ $codigo }}</td>
                <td class="b-fecha">Fecha : {{ $fecha }}</td>
                <td style="width: 100%;"></td> <!-- Spacer -->
            </tr>
        </table>
    </div>

    <!-- Matriz de Datos -->
    <table class="matrix">
        <thead>
            <tr>
                <th class="row-header" style="background-color: #ef4444;"></th>
                @foreach ($uniqueCylinders as $i => $cylinder)
                    <th scope="col" class="col-header @if($i == 8 || $i == 16) border-right-thick @endif">{{ number_format($cylinder, 2) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $rowIndex => $row)
                @php 
                    // $thickRowBorder = ($rowIndex + 1) % 5 == 0 ? 'border-bottom-thick' : '';
                    $thickRowBorder = ($rowIndex == 4 || $rowIndex == 8 || $rowIndex == 16)? 'border-bottom-thick' : '';
                @endphp
                <tr>
                    @foreach($row as $i => $opticalProperty)
                        @if($opticalProperty)
                            <th class="row-header {{ $thickRowBorder }}" style="@if(strcmp($type, "+") == 0) background-color: blue; @else background-color: red; @endif">
                                {{ number_format($opticalProperty['sphere'], 2) }}
                            </th>
                            @break
                        @endif
                    @endforeach
                    @foreach($row as $colIndex => $opticalProperty)
                        @php 
                            $thickColBorder = ($colIndex == 8 || $colIndex == 16) ? 'border-right-thick' : '';
                        @endphp
                        
                        <td class="{{ $thickColBorder }} {{ $thickRowBorder }} val-num">

                        @if(!empty($opticalProperty['amount']))
                            {{ $opticalProperty['amount'] }}
                        @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>