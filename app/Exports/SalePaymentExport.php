<?php

namespace App\Exports;

use App\Models\SalePayment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class SalePaymentExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithCustomStartCell
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    /**
     * Definimos el punto de inicio en la celda A3 para dejar espacio al título.
     */
    public function startCell(): string
    {
        return 'A3';
    }

    /**
     * Mapeo de datos de las columnas.
     */
    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->sale?->date_sale ?? 'N/A',
            $payment->sale?->customer?->name ?? 'N/A',
            number_format($payment->amount, 2),
            $payment->payment_method,
            $payment->created_at->format('d/m/Y'),
        ];
    }

    /**
     * Encabezados de la tabla (fila 3).
     */
    public function headings(): array
    {
        return [
            'ID Pago',
            'Fecha Venta',
            'Cliente',
            'Monto',
            'Método',
            'Fecha Pago',
        ];
    }

    /**
     * Aplicación de estilos: Título, Unión de celdas y Colores.
     */
    public function styles(Worksheet $sheet)
    {
        // 1. Insertamos el título manualmente en la celda A1
        $sheet->setCellValue('A1', 'Reporte de Pagos');

        // 2. Unimos las primeras 5 columnas (A1 hasta E1)
        $sheet->mergeCells('A1:E1');

        // 3. Aplicamos estilo al título: 14px, Negrita, Color Azul
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['argb' => '0000FF'], // Azul puro
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Estilo para los encabezados de la tabla (fila 3)
        $sheet->getStyle('A3:F3')->getFont()->setBold(true);

        return [];
    }
}