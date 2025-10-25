<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\CashBoxClosing;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Price;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\SaleItemService;
use App\Models\SalePayment;
use App\Models\Service;
use App\Services\SaleService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use function PHPUnit\Framework\isNull;

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
    public $paymentType = SalePayment::METHOD_CASH;

    // Customer select
    public $customer;

    public $canTypeMayor = false;
    public $isSaleCredit = false;

    public $promoActive = true;
    public $selectedPromo = false;
    public $promotionActives = [];
    public $promotion = null;
    public $message_error = null;
    public $isOpenCashBoxClosing = false;

    public function mount($branchId): void
    {
        $this->branch = Branch::find($branchId);
        $this->categories = Category::where('is_active', true)->get();

        $this->promotionActives = Promotion::active()->get();
        $this->promoActive = (count($this->promotionActives) > 0)? true : false;

        $this->isOpenCashBoxClosing = CashBoxClosing::isOpenCashBoxByBranchAndUser($branchId, Auth::id());
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

        $services = Service::where("is_active", true)->orderBy('name')->get();

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
                $this->cart[$cartKey]['quantity'] = (is_numeric($this->cart[$cartKey]['quantity']))? $this->cart[$cartKey]['quantity']+1 : 1;
                $this->cart[$cartKey]['limit'] = $quantity;
                $this->cart[$cartKey]['promotion'] = null;
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
                'promotion' => null,
                'limit' => $quantity,
                'type'     => $type,
            ];
        }
        $this->calculateTotals();
    }

    public function addServiceToProduct($key, $itemId, $serviceId)
    {
        $canIncreaseService = true;
        $cartKey = null;
        if($this->cart[$key]['id'] == $itemId && $this->cart[$key]['type'] === 'product'){
            if(isset($this->cart[$key]['services']) && count($this->cart[$key]['services']) > 0){
                foreach ($this->cart[$key]['services'] as $subItem){
                    if($subItem['id'] == $serviceId){
                        $canIncreaseService = false;
                    }
                }
            }
        }
        if($canIncreaseService){
            /** @var Service $serviceTemp */
            $serviceTemp = Service::find($serviceId);
            $this->cart[$key]['services']['sub-' . $itemId . '-' . $serviceId] = [
                'id'       => $serviceId,
                'name'     => $serviceTemp->name,
                'price'    => $serviceTemp->getPriceByType($this->branch->id, $this->saleType),
                'quantity' => $this->cart[$key]['quantity'],
                'promotion' => (isset($this->promotion))? $this->promotion->discount_percentage : null,
                'limit' => $this->cart[$key]['quantity'],
            ];
        }
        $this->calculateTotals();
    }

    // Actualiza la cantidad de un ítem en el carrito
    public function updateCartQuantity($cartKey, $quantity)
    {
        if (isset($this->cart[$cartKey])) {
            if($quantity <= 0){
                $this->cart[$cartKey]['quantity'] = 1;
                $this->calculateTotals();
            }

            if($quantity < $this->cart[$cartKey]['limit']){
                $this->cart[$cartKey]['quantity'] = max(1, (int)$quantity);
                $this->calculateTotals();
            }

            if(isset($this->cart[$cartKey]['services']) && count($this->cart[$cartKey]['services']) > 0){
                foreach ($this->cart[$cartKey]['services'] as $key => $sub){
                    $this->cart[$cartKey]['services'][$key]['quantity'] = $this->cart[$cartKey]['quantity'];
                    $this->cart[$cartKey]['services'][$key]['limit'] = $this->cart[$cartKey]['limit'];
                }
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

    public function updatedSelectedPromo(){
        if($this->selectedPromo){
            $this->promotion = Promotion::find($this->selectedPromo);
        } else {
            $this->promotion = null;
        }
        $this->calculateTotals();
    }

    // Elimina un ítem del carrito
    public function removeFromCart($cartKey)
    {
        unset($this->cart[$cartKey]);
        $this->calculateTotals();
    }

    // Elimina un subitem/servicio de un producto
    public function removeSubFromCart($cartKey, $subKey)
    {
        unset($this->cart[$cartKey]['services'][$subKey]);
        $this->calculateTotals();
        return;
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
        $this->promoItems();
        $this->subtotal = collect($this->cart)->sum(function ($item) {
            $subServicesTotal = 0;
            // Calculamos los subservicios de cada producto
            if(isset($item['services']) && count($item['services']) > 0){
                foreach ($item['services'] as $sub){
                    if($sub['promotion']){
                        $subServicesTotal += ($sub['price'] - ($sub['price'] * $sub['promotion']/100)) * $sub['quantity'];
                    } else {
                        $subServicesTotal += $sub['price'] * $sub['quantity'];
                    }
                }
            }


            if($item['promotion']){
                return (($item['price'] - ($item['price'] * $item['promotion']/100)) * $item['quantity']) + $subServicesTotal;
            }
            return ($item['price'] * $item['quantity']) + $subServicesTotal;
        });

        $this->discountAmount = ($this->subtotal * (float)$this->discountPercentage) / 100;
        if(($this->subtotal - $this->discountAmount) < 0){
            $this->discountAmount = 0;
            $this->discountPercentage =0;
        }
        $this->total = $this->subtotal - $this->discountAmount;
    }

    public function promoItems(){
        foreach ($this->cart as $key => $cart){
            $itemTemp = Product::find($this->cart[$key]['id']);
            if(strcmp($this->cart[$key]['type'], 'service') == 0){
                $itemTemp = Service::find($this->cart[$key]['id']);
            }

            $itemPromotion = null;
            if($this->promotion){
                $pPromotion = $itemTemp->getPromotionById($this->promotion->id);
                if($pPromotion != null){
                    $itemPromotion = $pPromotion->discount_percentage;
                }
            }
            $this->cart[$key]['promotion'] = $itemPromotion;

            if(strcmp($this->cart[$key]['type'], 'service') != 0){
                // En este if actualizamos la promocion de los servicio que tiene cada producto
                if(isset($this->cart[$key]['services']) && count($this->cart[$key]['services']) > 0){
                    foreach ($this->cart[$key]['services'] as $keySub => $sub){
                        $itemP = null;
                        if($this->promotion){
                            /** @var Service $itemS */
                            $itemS = Service::find($sub['id']);
                            $pPromotion = $itemS->getPromotionById($this->promotion->id);
                            if($pPromotion != null){
//                                dd('ddd: ' . $this->promotion);
                                $itemP = $pPromotion->discount_percentage;
                            }
                        }
                        $this->cart[$key]['services'][$keySub]['promotion'] = $itemP;
                    }
                }
            }

        }

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
    public function completePayment(SaleService $saleService)
    {
        $this->message_error = "";
        if(count($this->cart) == 0){
            $this->message_error = 'Debe seleccionar productos / servicios a vender';
            return;
        }
        if($this->total < 0){
            $this->message_error = 'El Total de venta no puede ser un numero negativo';
            return;
        }
        if(!isset($this->customer)){
            $this->message_error = 'Debe elegir un cliente para la venta';
            return;
        }


        $userId = auth()->id();
        $total = $this->total;
        // 1. Preparar los datos del DETALLE de la venta (SaleItem)
        $itemsData = [];
        foreach ($this->cart as $item) {
            // Buscamos sub servicios por producto
            $subServices = [];
            if(isset($item['services']) && count($item['services']) > 0){
                foreach ($item['services'] as $key => $sub){
                    $subServices[] = [
                        'service_id' => $sub['id'],
                        'quantity' => $sub['quantity'],
                        'price_per_unit' => $sub['price'],
                        'promotion_id' => (isset($sub['promotion']))? $this->promotion->id : null,
                        'promotion_discount_rate' => $sub['promotion'],
                        'subtotal' => SaleItemService::calculateSubtotal($sub['quantity'], $sub['price'], $sub['promotion'])
                    ];
                }
            }

            // Se transforma la estructura del carrito a la estructura de la tabla SaleItem
            $itemsData[] = [
                'salable_id' => $item['id'],
                'salable_type' => $item['type'],
                'quantity' => $item['quantity'],
                'base_price' => $item['price'],
                'promotion_id' => (isset($item['promotion']))? $this->promotion->id : null,
                'promotion_discount_rate' => (isset($item['promotion']))? $this->promotion->discount_percentage : 0,
                'final_price_per_unit' => (isset($item['promotion']))? $item['price'] - ($item['price'] * ($this->promotion->discount_percentage/100)) : $item['price'],
                'subtotal' => (isset($item['promotion']))? ($item['price'] - ($item['price'] * ($this->promotion->discount_percentage/100))) * $item['quantity'] : $item['price'] * $item['quantity'],
                'services' => $subServices
            ];
        }

        // 2. Preparar los datos del ENCABEZADO de la venta
        $saleData = [
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'user_id' => $userId,
            'cash_box_closing_id' => Branch::find($this->branch->id)->getCashBoxClosingByUser($userId)->id,
            'total_amount' => $this->subtotal,
            'final_discount' => $this->discountPercentage,
            'final_total' => $this->subtotal - ($this->subtotal * ($this->discountPercentage/100)),
            'status' => ($this->isSaleCredit)? Sale::SALE_STATUS_PARTIAL_PAYMENT : Sale::SALE_STATUS_PAID,
            'payment_method' => $this->paymentType,
            'sale_type' => $this->saleType,
            'paid_amount' => ($this->isSaleCredit)? $this->partial_payment : null,
            'due_amount' => ($this->isSaleCredit)? $this->total - $this->partial_payment : null,
            'items' => $itemsData,
        ];

        try {

            // 3. LLAMADA CRÍTICA AL SERVICIO
            // El servicio se encarga de: Iniciar DB::transaction, crear Sale, crear SaleItems,
            // y por cada Product llamar al InventoryService para el descuento.
            $sale = $saleService->createSale(
                $saleData
            );

            // Éxito:
            Notification::make()
                ->title('Exito')
                ->body("¡Venta N° {$sale->id} registrada con éxito! Stock descontado.")
                ->success()
                ->send();
            $this->reset(['cart', 'customer', 'selectedPromo', 'discountPercentage', 'saleType', 'paymentType']);
            // Puedes emitir un evento para imprimir la factura aquí
            $this->cart = [];
            $this->calculateTotals();

        } catch (\Exception $e) {
            // Fracaso: El servicio hizo Rollback.
            Log::info($e->getTraceAsString());
            Notification::make()
                ->title('Error')
                ->body("Error al procesar la venta: " . $e->getMessage())
                ->danger()
                ->send();
        }
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
        // Verificar si el cliente puede comparar al credito
        $this->partial_payment = 0;
        $this->isSaleCredit = false;

    }
}
