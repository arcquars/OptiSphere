<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Models\WarehouseIncome;
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

class ProductEntry extends Component implements HasForms
{
    use InteractsWithForms, WithPagination;

    public $warehouseId;
    public bool $showForm = false;
    public string $searchQuery = '';
    public Collection $selectedProducts;
    public Collection $productQuantities;


    protected string $paginationTheme = 'tailwind';

    protected $listeners = ['productSelected' => 'addProduct'];

    public function mount($warehouseId): void
    {
        $this->warehouseId = $warehouseId;
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
            $this->selectedProducts = collect();
            $this->productQuantities = collect();
        }
    }

    public function searchProducts()
    {
        if (strlen($this->searchQuery) < 3) {
            return Collection::empty(); // Devuelve una paginación vacía si no hay búsqueda
        }

        return Product::where('name', 'like', '%' . $this->searchQuery . '%')
            ->orWhere('code', 'like', '%' . $this->searchQuery . '%')
            ->paginate(5); // Pagina los resultados de la búsqueda
    }

    public function searchCode()
    {
        $product = Product::where('code', $this->searchQuery)->first();
        if($product){
            $this->addProduct($product->id);
        }
        $this->searchQuery = "";
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

    public function saveEntry(): void
    {
        DB::transaction(function () {
            $warehouseIncome = WarehouseIncome::create([
                'warehouse_id' => $this->warehouseId,
                'user_id' => Auth::id(),
                'income_date' => Carbon::now()
            ]);

            foreach ($this->productQuantities as $productId => $quantity) {
                if ($quantity > 0) {
                    $stockId = $productId;
                    // 'amount' es la nueva cantidad que viene del input.
                    $amount = (int) $quantity;

                    $attributes = [
                        'product_id' => $stockId,
                        'warehouse_id' => $this->warehouseId, // Ejemplo: asume que es el almacén 1
                    ];
                    // Buscar el registro de stock por su ID.
                    $warehouseStock = WarehouseStock::firstOrCreate($attributes, [
                        'quantity' => 0 // Inicializa la cantidad en 0 si es un nuevo registro
                    ]);

                    $oldQuantity = $warehouseStock->quantity;
                    $newQuantity = $oldQuantity + $amount;

                    $warehouseStock->increment('quantity', $amount);

                    WarehouseStockHistory::create([
                        'warehouse_stock_id' => $warehouseStock->id,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'difference' => $amount,
                        'movement_type' => WarehouseStockHistory::MOVEMENT_TYPE_INCOME,
                        'type_id' => $warehouseIncome->id
                    ]);
                }
            }
        });


        $this->selectedProducts = collect();
        $this->productQuantities = collect();
        $this->showForm = false;
//        session()->flash('success', 'Productos ingresados al inventario correctamente.');
        Notification::make()
            ->title('Éxito')
            ->body('Productos ingresados al inventario correctamente.')
            ->success()
            ->send();
        $this->showForm = false;
    }

    public function render()
    {
        $searchResults = $this->searchProducts();
        return view('livewire.warehouse.product-entry', [
            'searchResults' => $searchResults,
        ]);
    }
}
