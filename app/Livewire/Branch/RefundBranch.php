<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseRefund;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class RefundBranch extends Component  implements HasForms
{
    use InteractsWithForms, WithPagination;

    public $branch;
    public bool $showForm = false;
    public string $searchQuery = '';
    public bool $searchByCode = false;

    public Collection $selectedProducts;
    public Collection $productQuantities;

    public $message = "";
    public $warehouseSelect;

    protected string $paginationTheme = 'tailwind';

    protected $listeners = ['productSelected' => 'addProduct'];

    public function mount($branchId): void
    {
        $this->branch = Branch::find($branchId);
        $this->selectedProducts = collect();
        $this->productQuantities = collect();
    }

    public function updatingSearchQuery(): void
    {
        $this->resetPage();
    }

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        if($this->showForm){
            $this->searchQuery = '';
            $this->message = '';
            $this->selectedProducts = collect();
            $this->productQuantities = collect();
        }
    }

    public function searchProducts()
    {
        if (strlen($this->searchQuery) < 3) {
            return Collection::empty();
        }

        return Product::where('name', 'like', '%' . $this->searchQuery . '%')
            ->orWhere('code', 'like', '%' . $this->searchQuery . '%')
            ->with('stocks', function ($q){
                $q->where('branch_id', $this->branch->id);
            })->paginate(5); // Pagina los resultados de la búsqueda
    }

    public function searchCode()
    {
        $product = Product::where('code', $this->searchQuery)->first();
        if($product){
            $this->addProduct($product->id);
        }
    }

    public function addProduct(int $productId): void
    {
        $product = Product::find($productId);
        if ($product && !$this->selectedProducts->contains('id', $product->id)) {
            $this->selectedProducts->push($product);
            $this->productQuantities->put($product->id, 1);
        }
        $this->searchQuery = '';
    }

    public function removeProduct(int $productId): void
    {
        $this->selectedProducts = $this->selectedProducts->filter(fn($product) => $product->id !== $productId);
        $this->productQuantities->forget($productId);
    }

    public function saveRefund(): void
    {
        if(!$this->validateProductQuantities()){
            return;
        }
        DB::transaction(function () {
            $warehouseRefund = WarehouseRefund::create([
                'warehouse_id' => $this->warehouseSelect,
                'branch_id' => $this->branch->id,
                'user_id' => Auth::id(),
                'refund_date' => Carbon::now()
            ]);
            foreach ($this->productQuantities as $productId => $quantity) {
                if ($quantity > 0) {

                    $stockId = $productId;
                    // 'amount' es la nueva cantidad que viene del input.
                    $amount = (int) $quantity;

                    $attributes = [
                        'product_id' => $stockId,
                        'warehouse_id' => $this->warehouseSelect, // Ejemplo: asume que es el almacén 1
                    ];
                    // Buscar el registro de stock por su ID.
                    $warehouseStock = WarehouseStock::firstOrCreate($attributes, [
                        'quantity' => 0 // Inicializa la cantidad en 0 si es un nuevo registro
                    ]);

                    $oldQuantity = $warehouseStock->quantity;
                    $newQuantity = $oldQuantity + $amount;

                    $warehouseStock->increment('quantity', $amount);

                    if ($amount != 0) {
                        WarehouseStockHistory::create([
                            'warehouse_stock_id' => $warehouseStock->id,
                            'old_quantity' => $oldQuantity,
                            'new_quantity' => $newQuantity,
                            'difference' => $amount,
                            'movement_type' => WarehouseStockHistory::MOVEMENT_TYPE_REFUND,
                            'type_id' => $warehouseRefund->id
                        ]);
                    }
                    // Buscar registro de stock en sucursal
                    $attrProd = [
                        'product_id' => $stockId,
                        'branch_id' => $this->branch->id
                    ];
                    $productStock = ProductStock::firstOrCreate($attrProd, [
                        'quantity' => 0 // Inicializa la cantidad en 0 si es un nuevo registro
                    ]);;

                    $oldQuantity = $productStock->quantity;
                    $newQuantity = $oldQuantity - $amount;

                    $productStock->increment('quantity', ($amount * (-1)));

                    if ($amount != 0) {
                        InventoryMovement::create([
                            'product_id' => $stockId,
                            'from_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                            'from_location_id' => $this->branch->id,
                            'to_location_type' => InventoryMovement::LOCATION_TYPE_warehouse,
                            'to_location_id' => $this->warehouseSelect,
                            'old_quantity' => $oldQuantity,
                            'new_quantity' => $newQuantity,
                            'difference' => $amount,
                            'type' => WarehouseStockHistory::MOVEMENT_TYPE_REFUND,
                            'user_id' => Auth::id(),
                        ]);
                    }
                }
            }
        });


        $this->selectedProducts = collect();
        $this->productQuantities = collect();
        $this->searchQuery = '';
        Notification::make()
            ->title('Éxito')
            ->body('Productos devueltos al inventario correctamente.')
            ->success()
            ->send();
        $this->showForm = false;
    }

    public function validateProductQuantities(): bool
    {
        $valid = true;
        $this->message = "";
        foreach ($this->productQuantities as $productId => $quantity){
            $tempProduct = Product::find($productId);
            $productStock = $tempProduct->stockByStockBranch($this->branch->id);
            if(isset($productStock) && $productStock->quantity < $quantity){
                $valid = false;
                $this->message = "Tiene cantidades mayores a las cantidades en stock.";
                continue;
            }
        }

        return $valid;
    }

    public function render()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $searchResults = $this->searchProducts();
        return view('livewire.branch.refund-branch', [
            'searchResults' => $searchResults,
            'warehouses' => $warehouses
        ]);
    }
}
