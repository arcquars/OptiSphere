<?php

namespace App\Livewire\Sale;

use App\Models\Branch;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

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

    public $branches;

    public function mount(): void
    {
        $this->dateStart = now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = now()->endOfMonth()->format('Y-m-d');

        if(auth()->user()->hasRole('admin')){
            $this->branches = Branch::where('is_active', true)->get();
        } elseif (auth()->user()->hasRole('branch-manager')) {
            $this->branches = User::find(Auth::id())->branches;
        }
    }

    public function search(){
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

        // 2️⃣ Diferencia máxima de 30 días
        if ($start->diffInDays($end) > 30) {
            $this->addError('dateEnd', 'El rango no puede ser mayor a 30 días.');
            return;
        }
    }
    public function render()
    {
        // Iniciar la consulta base, cargando relaciones para optimizar.
        // Asumo que tu modelo 'Sale' tiene relaciones 'branch' y 'customer'.
        $query = Sale::with(['branch', 'customer']);

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

        if($this->statusSelect && $this->statusSelect !== 'all'){
            $query->where('status', 'like', $this->statusSelect);
        }

        // Clonar la consulta ANTES de la paginación para calcular los KPIs
        $kpiQuery = clone $query;

        // Calcular los KPIs
        $totalSales = $kpiQuery->sum('final_total');
        $creditSales = (clone $kpiQuery)->where('status', 'CREDIT')->sum('final_total');
        $transactionCount = $kpiQuery->count();
        $promoCount = 0;
        foreach ($kpiQuery->get() as $kpi){
            if($kpi->use_promotion)
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
}
