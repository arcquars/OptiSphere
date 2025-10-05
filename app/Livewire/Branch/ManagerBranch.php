<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Price;
use App\Models\Product;
use App\Models\Service;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ManagerBranch extends Component
{
    use WithPagination;
    public $branch;
    public $categories;
    public $searchResults = [];

    // Estado para la búsqueda y catálogos
    public $searchTerm = '';
    public $selectedCategory = '';
    public $activeTab = 'products'; // 'products' o 'services'

    // Estado para el carrito de compras
    public $cart = [];
    public $discountPercentage = 0;
    public $subtotal = 0;
    public $discountAmount = 0;
    public $total = 0;
    public $partial_payment = 0;

    // Estado para las opciones de la venta
    public $saleType = Price::TYPE_NORMAL;
    public $paymentType = 'Efectivo';

    // Customer select
    public $customer;

    public $canTypeMayor = false;

    public $promoActive = true;
    public $selectedPromo = true;

    public function mount($branchId): void
    {
        $this->branch = Branch::find($branchId);
        $this->categories = Category::where('is_active', true)->get();
        $this->calculateTotals();
    }

    public function render()
    {
        $query = Product::where("is_active", true)->where('name', 'like', '%'.$this->searchTerm.'%');
        $queryService = Service::where("is_active", true)->where('name', 'like', '%'.$this->searchTerm.'%');

        if(!empty($this->selectedCategory)){
            $query->whereHas('categories', function ($q) {
                // $q es un nuevo query builder, pero para el modelo Category
                // Asumimos que $this->selectedCategory es el ID de la categoría
                $q->where('id', $this->selectedCategory);
            });
            $queryService->whereHas('categories', function ($q) {
                // $q es un nuevo query builder, pero para el modelo Category
                // Asumimos que $this->selectedCategory es el ID de la categoría
                $q->where('id', $this->selectedCategory);
            });
        }
        $products1 = $query->orderBy('name')->paginate(5);
        $services1 = $queryService->orderBy('name')->paginate(5);

        $services = collect([
            ['id' => 1, 'name' => 'Servicio de Reparación', 'price' => 50.00],
            ['id' => 2, 'name' => 'Mantenimiento Preventivo', 'price' => 35.00],
        ]);

        return view('livewire.branch.manager-branch', [
//            'products' => $products,
            'products1' => $products1,
            'services' => $services,
            'services1' => $services1,
        ]);
    }


    // Cambia entre la pestaña de productos y servicios
    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }

    // Añade un ítem (producto o servicio) al carrito
    public function addToCart($itemId, $type, $name, $price, $quantity)
    {
        if($quantity == 0){
            Notification::make()
                ->title('Cuidado')
                ->body('El producto seleccionado no tiene items')
                ->warning()
                ->send();
            return;
        }

        $cartKey = $type . '-' . $itemId;

        if (isset($this->cart[$cartKey])) {
            if(($this->cart[$cartKey]['quantity'] + 1) <= $quantity) {
                $this->cart[$cartKey]['quantity']++;
                $this->cart[$cartKey]['limit'] = $quantity;
            } else {
                Notification::make()
                    ->title('Cuidado')
                    ->body('El producto tiene solo ' . $quantity . " para ser vendidos.")
                    ->warning()
                    ->send();
                return;
            }

        } else {
            $itemTemp = Product::find($itemId);
            if(strcmp($type, 'service') == 0){
                $itemTemp = Service::find($itemId);
            }

            $this->cart[$cartKey] = [
                'id'       => $itemId,
                'name'     => $name,
                'price'    => $itemTemp->getPriceByType($this->branch->id, $this->saleType),
                'quantity' => 1,
                'limit' => $quantity,
                'type'     => $type,
            ];
        }
        $this->calculateTotals();
    }

    // Actualiza la cantidad de un ítem en el carrito
    public function updateCartQuantity($cartKey, $quantity)
    {
        if (isset($this->cart[$cartKey])) {
            if($quantity < $this->cart[$cartKey]['limit']){
                $this->cart[$cartKey]['quantity'] = max(1, (int)$quantity);
                $this->calculateTotals();
            }
        }
    }

    public function updatedSaleType(){
        if(count($this->cart) > 0){
            foreach ($this->cart as $key => $cart){
                $itemTemp = Product::find($cart['id']);
                if(strcmp($cart['type'], 'service') == 0){
                    $itemTemp = Service::find($cart['id']);
                }
                $this->cart[$key]['price'] = $itemTemp->getPriceByType($this->branch->id, $this->saleType);
                Log::info("Pdm price update::: " . $cart['price']);
            }
            $this->calculateTotals();
        }

    }

    // Elimina un ítem del carrito
    public function removeFromCart($cartKey)
    {
        unset($this->cart[$cartKey]);
        $this->calculateTotals();
    }

    // Escucha los cambios en discountPercentage y recalcula
    public function updatedDiscountPercentage()
    {
        $this->calculateTotals();
    }

    public function applyDiscount(){
        $this->calculateTotals();
    }

    // Calcula todos los totales del carrito
    public function calculateTotals()
    {
        $this->subtotal = collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $this->discountAmount = ($this->subtotal * (float)$this->discountPercentage) / 100;
        $this->total = $this->subtotal - $this->discountAmount;
    }

    // Guarda un nuevo cliente desde el modal
    public function saveCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|string|max:255',
            'newCustomerNit' => 'required|string|max:20',
            'newCustomerEmail' => 'nullable|email|max:255',
        ]);

        // Lógica para guardar en la base de datos
        // Customer::create([ ... ]);

        session()->flash('message', 'Cliente guardado exitosamente.');
        $this->dispatchBrowserEvent('close-customer-modal'); // Emite un evento JS para cerrar el modal
        $this->reset(['newCustomerName', 'newCustomerNit', 'newCustomerEmail']);
    }

    // Lógica para finalizar la venta
    public function completePayment()
    {
        // Lógica para crear la orden, registrar el pago, etc.
        session()->flash('message', '¡Venta completada exitosamente!');
        $this->cart = [];
        $this->calculateTotals();
    }

    public function updatedSearchTerm($value)
    {
        if (strlen($value) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->resetPage();
        // En una aplicación real, aquí buscarías en la base de datos
        // $products = Product::where('name', 'like', '%'.$value.'%')->take(5)->get();
        // $services = Service::where('name', 'like', '%'.$value.'%')->take(5)->get();

        // Para este ejemplo, filtramos las colecciones de prueba
        $allProducts = collect([
            ['id' => 1, 'name' => 'Gafas Sol Adidas', 'price' => 1.50, 'image' => 'https://placehold.co/200x200/F2C14E/FFFFFF?text=Producto', 'type' => 'product'],
            ['id' => 2, 'name' => 'Anteojos co-85', 'price' => 2.00, 'image' => 'https://placehold.co/200x200/4EC1F2/FFFFFF?text=Producto', 'type' => 'product'],
        ]);
        $allServices = collect([
            ['id' => 1, 'name' => 'Servicio de Reparación', 'price' => 50.00, 'type' => 'service'],
            ['id' => 2, 'name' => 'Mantenimiento Preventivo', 'price' => 35.00, 'type' => 'service'],
        ]);

        $filteredProducts = $allProducts->filter(fn($p) => str_contains(strtolower($p['name']), strtolower($value)));
        $filteredServices = $allServices->filter(fn($s) => str_contains(strtolower($s['name']), strtolower($value)));

        $this->searchResults = $filteredProducts->merge($filteredServices)->take(5)->all();
    }

    public function selectAndAddToCart($itemId, $type)
    {
        // Lógica para encontrar el item y añadirlo al carrito...
        // (Esto es una simulación, en la vida real buscarías en la BD)
        if ($type === 'product') {
            $item = collect([
                ['id' => 1, 'name' => 'Gafas Sol Adidas', 'price' => 1.50],
                ['id' => 2, 'name' => 'Anteojos co-85', 'price' => 2.00],
            ])->firstWhere('id', $itemId);
        } else {
            $item = collect([
                ['id' => 1, 'name' => 'Servicio de Reparación', 'price' => 50.00],
                ['id' => 2, 'name' => 'Mantenimiento Preventivo', 'price' => 35.00],
            ])->firstWhere('id', $itemId);
        }

        if ($item) {
            $this->addToCart($item['id'], $type, $item['name'], $item['price']);
        }

        // Limpiar la búsqueda después de seleccionar
        $this->searchTerm = '';
        $this->searchResults = [];
    }

    #[On('customer-updated')]
    public function updateCustomer($id)
    {
        $this->customer = Customer::find($id);

        if(strcmp($this->customer->type, Customer::TYPE_MAYORISTA) == 0){
//            $this->saleType = Customer::TYPE_MAYORISTA;
            $this->canTypeMayor = true;
        } else {
            $this->saleType = Customer::TYPE_NORMAL;
            $this->canTypeMayor = false;
        }
    }
}
