<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseIncome;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class HistoryProductModal extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public ?Product $product = null;
    public Warehouse $warehouse;

    protected $listeners = ['toggleOpenHistoryProduct' => 'openHistoryProductModal'];

    public function mount(int $warehouseId): void
    {
        $this->warehouse = Warehouse::find($warehouseId);
    }

    public function openHistoryProductModal(int $productId): void
    {
        $this->product = Product::find($productId);
        $this->resetPage('movsPage');
        $this->showForm = !$this->showForm;
    }

    public function closeModal(): void
    {
        $this->showForm = false;
    }

    public function render()
    {
        $movements = Collection::empty();

        if ($this->product) {
            $whId      = $this->warehouse->id;
            $productId = $this->product->id;

            // ------------------------------------------------------------------
            // INGRESO
            // ------------------------------------------------------------------
            $incomes = DB::table('warehouse_incomes as wi')
                ->select(
                    'wi.id',
                    'wi.income_date as date',
                    DB::raw("'INGRESO' as movement_label"),
                    'wi.user_id',
                    'wi.warehouse_id',
                    DB::raw('NULL as branch_id'),
                    'p.name as product_name',
                    'wsh.old_quantity',
                    'wsh.new_quantity',
                    DB::raw('(wsh.new_quantity - wsh.old_quantity) as difference')  
                )
                ->join('warehouse_stock_histories as wsh', function ($join) {
                    $join->on('wsh.type_id', '=', 'wi.id')
                        ->where('wsh.movement_type', '=', 'INGRESO');
                })
                ->join('warehouse_stocks as ws', 'ws.id', '=', 'wsh.warehouse_stock_id')
                ->join('products as p', 'p.id', '=', 'ws.product_id')
                ->where('wi.warehouse_id', $whId)
                ->where('p.id', $productId)
                // Agrega p.name al GROUP BY — requerido por only_full_group_by
                ->groupBy('wi.id', 'wi.income_date', 'wi.user_id', 'wi.warehouse_id', 'p.name', 'wsh.old_quantity', 'wsh.new_quantity');

            // ------------------------------------------------------------------
            // ENTREGA
            // ------------------------------------------------------------------
            $deliveries = DB::table('warehouse_deliveries as wd')
                ->select(
                    'wd.id',
                    'wd.delivery_date as date',
                    DB::raw("'ENTREGA' as movement_label"),
                    'wd.user_id',
                    'wd.warehouse_id',
                    'wd.branch_id',
                    'p.name as product_name',
                    'wsh.old_quantity',
                    'wsh.new_quantity',
                    DB::raw('(wsh.new_quantity - wsh.old_quantity) as difference')  
                )
                ->join('warehouse_stock_histories as wsh', function ($join) {
                    $join->on('wsh.type_id', '=', 'wd.id')
                        ->where('wsh.movement_type', '=', 'ENTREGA_SUCURSAL');
                })
                ->join('warehouse_stocks as ws', 'ws.id', '=', 'wsh.warehouse_stock_id')
                ->join('products as p', 'p.id', '=', 'ws.product_id')
                ->where('wd.warehouse_id', $whId)
                ->where('p.id', $productId)
                // Agrega p.name y wd.branch_id al GROUP BY
                ->groupBy('wd.id', 'wd.delivery_date', 'wd.user_id', 'wd.warehouse_id', 'wd.branch_id', 'p.name', 'wsh.old_quantity', 'wsh.new_quantity');

            // ------------------------------------------------------------------
            // DEVOLUCION
            // ------------------------------------------------------------------
            $refunds = DB::table('warehouse_refunds as wr')
                ->select(
                    'wr.id',
                    'wr.refund_date as date',
                    DB::raw("'DEVOLUCION' as movement_label"),
                    'wr.user_id',
                    'wr.warehouse_id',
                    'wr.branch_id',
                    'p.name as product_name',
                    'wsh.old_quantity',
                    'wsh.new_quantity',
                    DB::raw('(wsh.new_quantity - wsh.old_quantity) as difference')  
                )
                ->join('warehouse_stock_histories as wsh', function ($join) {
                    $join->on('wsh.type_id', '=', 'wr.id')
                        ->where('wsh.movement_type', '=', 'DEVOLUCION');
                })
                ->join('warehouse_stocks as ws', 'ws.id', '=', 'wsh.warehouse_stock_id')
                ->join('products as p', 'p.id', '=', 'ws.product_id')
                ->where('wr.warehouse_id', $whId)
                ->where('p.id', $productId)
                // Agrega p.name y wr.branch_id al GROUP BY
                ->groupBy('wr.id', 'wr.refund_date', 'wr.user_id', 'wr.warehouse_id', 'wr.branch_id', 'p.name', 'wsh.old_quantity', 'wsh.new_quantity');

            // ------------------------------------------------------------------
            // UNION ALL + paginación sobre la subconsulta
            // ------------------------------------------------------------------
            $unionQuery = $incomes
                ->unionAll($deliveries)
                ->unionAll($refunds);

            $movements = DB::table(DB::raw("({$unionQuery->toSql()}) as history"))
                ->mergeBindings($unionQuery)
                ->leftJoin('users', 'history.user_id', '=', 'users.id')
                ->leftJoin('branches', 'history.branch_id', '=', 'branches.id')
                ->select(
                    'history.id',
                    'history.date',
                    'history.movement_label',
                    'history.user_id',
                    'history.warehouse_id',
                    'history.branch_id',
                    'history.product_name',
                    'users.name as user_name',
                    'branches.name as branch_name',
                    'history.difference',
                    'history.old_quantity',
                    'history.new_quantity'
                )
                ->orderBy('history.date', 'desc')
                ->paginate(
                    config('cerisier.pagination', 15),
                    ['*'],
                    'movsPage'
                );
        }

        return view('livewire.warehouse.history-product-modal', compact('movements'));
    }
}