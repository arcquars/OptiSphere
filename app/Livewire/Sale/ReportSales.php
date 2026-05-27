<?php

namespace App\Livewire\Sale;

use App\Models\Branch;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

use App\Exports\SalesReportExport;
use App\Models\SalePayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportSales extends Component
{
    use WithPagination, WithoutUrlPagination;

    #[Validate('required|date')]
    public $dateStart;
    #[Validate('required|date')]
    public $dateEnd;
    public $branchSelect = 'all';
    public $saleTypeSelect = 'all';
    public $statusSelect = 'all';
    public $typeSales;

    public $typePaymentSelect;

    public $typePayments;

    public $users;
    public $typeSale;
    public $userFilter;
    public $clientSearch;
    public $saleId;

    public $branches;
    public $isFacturado;

    public function mount(): void
    {
        $this->dateStart = now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = now()->endOfMonth()->format('Y-m-d');
        $this->typeSales = Sale::SALE_TYPE_SALES;
        $this->typePayments = SalePayment::PAYMENT_TYPES;
        $this->users = User::role('branch-manager')->get();
        if (auth()->user()->hasRole('admin')) {
            $this->branches = Branch::where('is_active', true)->get();
        } elseif (auth()->user()->hasRole('branch-manager')) {
            $this->branches = User::find(Auth::id())->branches;
        }
    }

    #[On('refresh-report-sales')]
    public function search()
    {
        $this->validate(); // valida las reglas de los atributos primero
        $this->resetPage();
        // ✅ Validación personalizada
        $start = Carbon::parse($this->dateStart);
        $end = Carbon::parse($this->dateEnd);

        // 1️⃣ Fecha de inicio menor a fin
        if ($start->gt($end)) {
            $this->addError('dateStart', 'La fecha de inicio debe ser menor que la fecha de fin.');
            return;
        }

        // 2️⃣ Diferencia máxima de 180 días
        if ($start->diffInDays($end) > 180) {
            $this->addError('dateEnd', 'El rango no puede ser mayor a 180 días.');
            return;
        }
    }
    public function render()
    {
        // Iniciar la consulta base, cargando relaciones para optimizar.
        // Asumo que tu modelo 'Sale' tiene relaciones 'branch' y 'customer'.
        $query = Sale::with(['branch', 'customer']);

        // Filtrar las ventas Anuladas VOIDED
        $query->where('status', '<>', Sale::SALE_STATUS_VOIDED);
        // Aplicar filtro de rango de fechas
        $query->whereBetween('date_sale', [$this->dateStart, Carbon::parse($this->dateEnd)->endOfDay()]);

        // Aplicar filtro de sucursal
        if ($this->branchSelect && $this->branchSelect !== 'all') {
            $query->where('branch_id', $this->branchSelect);
        }

        // Aplicar filtro de condición de venta (contado/crédito)
        // Asumo que tienes una columna 'payment_condition' en tu tabla 'sales'.
        if ($this->saleTypeSelect && $this->saleTypeSelect !== 'all') {
            $query->where('payment_condition', $this->saleTypeSelect);
        }

        if ($this->statusSelect && $this->statusSelect !== 'all') {
            $query->where('status', 'like', $this->statusSelect);
        }

        if ($this->typeSale) {
            $query->where('sale_type', '=', $this->typeSale);
        }

        if ($this->typePaymentSelect && $this->typePaymentSelect !== 'all') {
            $query->where('payment_method', '=', $this->typePaymentSelect);
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
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('nit', 'like', '%' . $search . '%');
                });
            })->with('customer');
        }
        // Clonar la consulta ANTES de la paginación para calcular los KPIs
        $kpiQuery = clone $query;

        // Calcular los KPIs
        $totalSales = $kpiQuery->sum('final_total');
        $creditSales = (clone $kpiQuery)->where('status', 'CREDIT')->sum('final_total');
        $transactionCount = $kpiQuery->count();
        $promoCount = 0;
        foreach ($kpiQuery->get() as $kpi) {
            if ($kpi->use_promotion)
                $promoCount++;
        }

        // Obtener los resultados paginados para la tabla
        $sales = $query->latest()->paginate(config('cerisier.pagination'));

        return view('livewire.sale.report-sales', [
            'sales' => $sales,
            'totalSales' => $totalSales,
            'creditSales' => $creditSales,
            'transactionCount' => $transactionCount,
            'promoCount' => $promoCount,
        ]);
    }

    /**
     * Valida filtros y retorna la query base con los filtros activos.
     * Reutilizada por exportPdf y exportExcel.
     */
    private function buildExportQuery()
    {
        // Reutiliza la misma lógica de filtros del render()
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

        if ($this->typePaymentSelect && $this->typePaymentSelect !== 'all') {
            $query->where('payment_method', '=', $this->typePaymentSelect);
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

    /**
     * Exporta el reporte a PDF usando dompdf v3.1.x
     * con los filtros activos en ese momento.
     */
    public function exportPdf(): mixed
    {
        $this->validate();

        $sales = $this->buildExportQuery()->get();

        if ($sales->isEmpty()) {
            $this->addError('dateStart', 'No hay ventas para exportar con los filtros actuales.');
            return null;
        }

        // Resuelve el nombre de la sucursal para mostrar en el PDF
        $branchName = null;
        if ($this->branchSelect && $this->branchSelect !== 'all') {
            $branchName = \App\Models\Branch::find($this->branchSelect)?->name;
        }

        $pdf = Pdf::loadView('pdf.sales-report-pdf', [
            'sales' => $sales,
            'dateStart' => $this->dateStart,
            'dateEnd' => $this->dateEnd,
            'branchName' => $branchName,
            'statusLabel' => $this->statusSelect !== 'all' ? $this->statusSelect : null,
            'typeSaleLabel' => $this->typeSale ?: null,
            'isFacturado' => $this->isFacturado,
        ])
            ->setPaper('letter', 'landscape')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isRemoteEnabled', false);

        $filename = 'reporte-ventas-' . $this->dateStart . '-al-' . $this->dateEnd . '.pdf';

        // En Livewire la descarga de archivos requiere streamDownload
        return response()->streamDownload(
            fn() => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Exporta el reporte a Excel con los filtros activos.
     * Maatwebsite Excel 3.1.x
     */
    public function exportExcel(): BinaryFileResponse
    {
        $this->validate();

        $filename = 'reporte-ventas-' . $this->dateStart . '-al-' . $this->dateEnd . '.xlsx';

        return Excel::download(
            new SalesReportExport(
                dateStart: $this->dateStart,
                dateEnd: $this->dateEnd,
                branchSelect: $this->branchSelect,
                saleTypeSelect: $this->saleTypeSelect,
                statusSelect: $this->statusSelect,
                typeSale: $this->typeSale,
                typePaymentSelect: $this->typePaymentSelect,
                userFilter: $this->userFilter,
                clientSearch: $this->clientSearch,
                saleId: $this->saleId,
                isFacturado: $this->isFacturado,
            ),
            $filename
        );
    }
}
