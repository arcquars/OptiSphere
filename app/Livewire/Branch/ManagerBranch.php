<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use Livewire\Component;

class ManagerBranch extends Component
{
    public $branch;
    public $searchResults = [];

    public function mount($branchId): void
    {
        $this->branch = Branch::find($branchId);
        $this->calculateTotals();
    }

    public function render()
    {
        // En una aplicación real, aquí filtrarías tus modelos
        // $products = Product::where('name', 'like', '%'.$this->searchTerm.'%')->get();
        // $services = Service::where('name', 'like', '%'.$this->searchTerm.'%')->get();

        // Para este ejemplo, usamos datos de prueba
        $products = collect([
            ['id' => 1, 'name' => 'Gafas Sol Adidas', 'price' => 1.50, 'image' => 'https://placehold.co/200x200/F2C14E/FFFFFF?text=Producto'],
            ['id' => 2, 'name' => 'Anteojos co-85', 'price' => 2.00, 'image' => 'https://placehold.co/200x200/4EC1F2/FFFFFF?text=Producto'],
        ]);
        $services = collect([
            ['id' => 1, 'name' => 'Servicio de Reparación', 'price' => 50.00],
            ['id' => 2, 'name' => 'Mantenimiento Preventivo', 'price' => 35.00],
        ]);

        return view('livewire.branch.manager-branch', [
            'products' => $products,
            'services' => $services,
        ]);
    }

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

    // Estado para las opciones de la venta
    public $saleType = 'Normal';
    public $paymentType = 'Efectivo';

    // Estado para la gestión de clientes
    public $customerSearch = '';
    public $selectedCustomer = null;

    // Propiedades para el modal de nuevo cliente
    public $newCustomerName = '';
    public $newCustomerNit = '';
    public $newCustomerEmail = '';


    // Cambia entre la pestaña de productos y servicios
    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }

    // Añade un ítem (producto o servicio) al carrito
    public function addToCart($itemId, $type, $name, $price)
    {
        $cartKey = $type . '-' . $itemId;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity']++;
        } else {
            $this->cart[$cartKey] = [
                'id'       => $itemId,
                'name'     => $name,
                'price'    => (float)$price,
                'quantity' => 1,
                'type'     => $type,
            ];
        }
        $this->calculateTotals();
    }

    // Actualiza la cantidad de un ítem en el carrito
    public function updateCartQuantity($cartKey, $quantity)
    {
        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity'] = max(1, (int)$quantity);
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
}
