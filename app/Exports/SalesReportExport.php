<?php

// app/Exports/SalesReportExport.php

namespace App\Exports;

use App\Models\Sale;
use App\Models\Price;
use App\Models\SalePayment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SalesReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    public function __construct(
        private string  $dateStart,
        private string  $dateEnd,
        private mixed   $branchSelect,
        private mixed   $saleTypeSelect,
        private mixed   $statusSelect,
        private mixed   $typeSale,
        private mixed   $userFilter,
        private mixed   $clientSearch,
        private mixed   $saleId,
        private mixed   $isFacturado,
    ) {}

    public function query()
    {
        $query = Sale::with(['branch', 'customer'])
            ->where('status', '<>', Sale::SALE_STATUS_VOIDED)
            ->whereBetween('date_sale', [
                $this->dateStart,
                Carbon::parse($this->dateEnd)->endOfDay(),
            ]);

        if ($this->branchSelect && $this->branchSelect !== 'all') {
            $query->where('branch_id', $this->branchSelect);
        }

        if ($this->saleTypeSelect && $this->saleTypeSelect !== 'all') {
            $query->where('payment_condition', $this->saleTypeSelect);
        }

        if ($this->statusSelect && $this->statusSelect !== 'all') {
            $query->where('status', 'like', $this->statusSelect);
        }

        if ($this->typeSale) {
            $query->where('sale_type', '=', $this->typeSale);
        }

        if ($this->userFilter) {
            $query->where('user_id', '=', $this->userFilter);
        }

        if ($this->saleId) {
            $query->where('id', '=', $this->saleId);
        }

        if ($this->isFacturado) {
            $query->whereNotNull('siat_invoice_id');
        }

        if ($this->clientSearch && !empty($this->clientSearch)) {
            $search = $this->clientSearch;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('nit', 'like', '%' . $search . '%');
            });
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'ID Venta',
            'Fecha',
            'Cliente',
            'NIT',
            'Sucursal',
            'Tipo Venta',
            'Método Pago',
            'Estado',
            'Promoción',
            'Facturado',
            'Total',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->id,
            $sale->date_sale->format('Y-m-d'),
            $sale->customer->name ?? '-',
            $sale->customer->nit ?? '-',
            $sale->branch->name ?? '-',
            strtoupper($sale->sale_type),
            $sale->payment_method,
            __('cerisier.' . $sale->status),
            $sale->use_promotion ? 'Sí' : 'No',
            isset($sale->siat_invoice_id) ? 'Sí' : 'No',
            $sale->final_total,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Encabezado con fondo azul y texto blanco
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Reporte de Ventas';
    }
}