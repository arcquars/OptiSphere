<?php

namespace App\Livewire\Branch;

use App\DTOs\CustomerSiatDto;
use App\DTOs\EventSiatDto;
use App\DTOs\InvoiceCreationDto;
use App\DTOs\PaymentQr;
use App\Helpers\ValidateSiatHelper;
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
use App\Services\AmyrEventsApiService;
use App\Services\CreditService;
use App\Services\EconomicoApiService;
use App\Services\MonoInvoiceApiService;
use App\Services\SaleService;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\Config\Exception\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use function PHPUnit\Framework\isNull;

class ManagerBranchCode extends Component
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
    public Customer $customer;

    public $canTypeMayor = false;
    public $isSaleCredit = false;

    public $promoActive = true;
    public $selectedPromo = false;
    public $promotionActives = [];
    public $promotion = null;
    public $message_error = null;
    public $isOpenCashBoxClosing = false;

    // PROPIEDADES PARA PAGO QR ---
    public $payment_method = 'Efectivo'; // Valor por defecto
    public $showQrModal = false;
    public $qrImage = null;
    public $qrId = null;
    public $isInvoicePending = false;

    public $eventSiatDto;
    public $qrModalMessage = "";

    public function mount($branchId): void
    {
        $this->branch = Branch::find($branchId);
        
        // dd($this->branch->is_facturable);
        $this->categories = Category::where('is_active', true)->get();
        $this->customer = new Customer();
        $this->promotionActives = Promotion::active()->get();
        $this->promoActive = (count($this->promotionActives) > 0)? true : false;

        $this->isOpenCashBoxClosing = CashBoxClosing::isOpenCashBoxByBranchAndUser($branchId, Auth::id());
        $this->calculateTotals();
        $this->setEventActive();
    }

    #[On('set-event-active')]
    public function setEventActive(){
        /**Obtener Evento activo si hay */
        $serviceEvent = new AmyrEventsApiService($this->branch->amyrConnectionBranch->token);
        $activeEvent = $serviceEvent->getEventActive($this->branch->amyrConnectionBranch->point_sale);
        if ($activeEvent && in_array($activeEvent['evento_id'], [5,6,7])) {
            $this->eventSiatDto = [
                "sucursal_id" => $activeEvent['sucursal_id'],
                "puntoventa_id" => $activeEvent['puntoventa_id'],
                "evento_id" => $activeEvent['evento_id'],
                "fecha_inicio" => $activeEvent['fecha_inicio'],
                "fecha_fin" => $activeEvent['fecha_fin'],
                "cafc" => $activeEvent['cafc'],
                "cufd_evento" => $activeEvent['cufd_evento']
            ];
        } else {
            $this->eventSiatDto = null;
        }
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
        $products1 = $query->orderBy('name')->simplePaginate(5);
        $services1 = $queryService->orderBy('name')->simplePaginate(5);

        $services = Service::where("is_active", true)->orderBy('name')->get();

        return view('livewire.branch.manager-branch-code', [
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
    public function addToCart($itemId, $type, $name, $price, int $quantity)
    {
        if($quantity == 0 && strcmp($type, 'service') != 0){
            Notification::make()
                ->title('Cuidado')
                ->body('El producto seleccionado no tiene items')
                ->warning()
                ->send();
            return;
        }

        $cartKey = $type . '-' . $itemId;

        if (isset($this->cart[$cartKey])) {
            if(strcmp($type, 'service') == 0){
                $this->cart[$cartKey]['quantity'] = (is_numeric($this->cart[$cartKey]['quantity']))? $this->cart[$cartKey]['quantity']+1 : 1;
                $this->cart[$cartKey]['limit'] = $quantity;
                $this->cart[$cartKey]['promotion'] = null;
            } else {
                Log::info("AddddddddCart product: cantidad en tienda: " . $quantity . " || cantidad en el carrito: " . $this->cart[$cartKey]['quantity']);
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

    public function scanCode($value = null)
    {
        $term = trim($value ?? $this->searchTerm);

        if (empty($term)) {
            return;
        }

        $product = Product::where('code', $term)
            ->where('is_active', true)
            ->first();

        if ($product) {
            $this->addToCart(
                $product->id, 
                'product', 
                $product->name, 
                $product->getPriceByType($this->branch->id, $this->saleType),
                $product->stockByBranch($this->branch->id)
            );
            $this->reset(['searchTerm', 'searchResults']); 
            
            Notification::make()->title('Agregado')->body($product->name)->success()->send();
            return;
        }

        $service = Service::where('code', $term)
            ->where('is_active', true)
            ->first();

        if ($service) {
            $this->addToCart(
                $service->id, 
                'service', 
                $service->name, 
                $service->getPriceByType($this->branch->id, $this->saleType),
                -1
            );
            $this->reset(['searchTerm', 'searchResults']); 
            Notification::make()->title('Agregado')->body($service->name)->success()->send();
            return;
        }

        // 3. Si no se encuentra el código
        Notification::make()
            ->title('No encontrado')
            ->body("No se encontró ningún ítem con el código: {$term}")
            ->warning()
            ->send();
            
        $this->reset(['searchTerm', 'searchResults']); 
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

    public function incrementCartQuantity($cartKey, $add = true)
    {
        if (isset($this->cart[$cartKey])) {
            $quantity = $this->cart[$cartKey]['quantity'] - 1;
            if($add){
                $quantity = $this->cart[$cartKey]['quantity'] + 1;
            }

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
    public function completePayment($isFacturable = false)
    {
        $creditService = new CreditService();
        $saleService = new SaleService($creditService);
        $siatData = [];
        if($isFacturable && ValidateSiatHelper::isValidSiatCode($this->cart) == false){
            $this->message_error = 'No se puede facturar, algunos productos o servicios no tienen datos SIAT completos.';
            return;
        }   
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

        if($this->isSaleCredit){
            $saldoTemp = number_format($this->customer->credit_limit - ($this->customer->saldo_credito + ($this->total - $this->partial_payment)), 2);
            Log::info("www ppp1:: " . $this->customer->credit_limit . " || " . $this->customer->saldo_credito . " || " . $this->total . " || " . $this->partial_payment);
            Log::info("www ppp2:: " . $saldoTemp);

            if($saldoTemp < 0){
                $this->message_error = 'El Cliente ' . $this->customer->name . " Sobre pasa su CREDITO :: " . $saldoTemp;
                return;
            }
            
        }

        if($isFacturable){
            $this->getCustomerSiatByNit();
            $resultSiat = $this->createSiatInvoice();
            Log::info("Resultado siat invoice::: ", ['result' => $resultSiat]);
            if($resultSiat == null){
                return;
            } else {
                $siatData = [
                    'siat_invoice_id' => $resultSiat['invoice_id'] ?? null,
                    'siat_status' => $resultSiat['status'] ?? null,
                ];
            }
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
        $saleData = array_merge($siatData, [
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
        ]);

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

            if($isFacturable && isset($siatData['siat_invoice_id']) && isset($siatData['siat_status']) && $siatData['siat_status'] == 'issued'){
                $pdfUrl = route('sales.invoice_pdf', $sale->id);
                
            } else {
                $pdfUrl = route('sales.receipt_pdf', $sale->id);
            }
            $this->dispatch('open-pdf', url: $pdfUrl);
            $this->dispatch('customer-clear-search');
            $this->searchTerm = "";
            $this->searchResults = [];
            $this->reset(['cart', 'customer', 'selectedPromo', 'discountPercentage', 'saleType', 'paymentType']);
            // Puedes emitir un evento para imprimir la factura aquí
            $this->cart = [];
            $this->calculateTotals();

        } catch (\Exception $e) {
            // Fracaso: El servicio hizo Rollback.
            Log::info($e->getTraceAsString());
            if($isFacturable && isset($siatData['siat_invoice_id']) && isset($siatData['siat_status']) && $siatData['siat_status'] == 'issued'){
                // Anular la factura en SIAT
                Log::info("Se debe anular la factura SIAT con ID: " . $siatData['siat_invoice_id']);
                $this->voidSiatInvoice($siatData['siat_invoice_id']);
            }
            Notification::make()
                ->title('Error')
                ->body("Error al procesar la venta: " . $e->getMessage())
                ->danger()
                ->send();
        }
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

    private function createSiatInvoice()
    {
        $invoiceApiService = new MonoInvoiceApiService($this->branch);
        $itemsData = [];
        foreach ($this->cart as $item) {
            // Se transforma la estructura del carrito a la estructura de la tabla SaleItem
            $productTemp = Product::find($item['id']);
            if(strcmp($item['type'], 'service') == 0){
                $productTemp = Service::find($item['id']);
            }

            $itemsData[] = [
                'product_id' => $productTemp->id,
                'product_code' => $productTemp->code,
                'product_name' => $productTemp->name,
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'unidad_medida' => $productTemp->siat_data_medida_code,
                'numero_seria'  => '',
                'numero_imei'   => '',
                'codigo_producto_sin' => $productTemp->siat_data_product_code,
                'codigo_actividad' => $productTemp->siat_data_actividad_code,
                'discount' => (isset($item['promotion']))? $item['price'] * ($item['promotion']/100) * $item['quantity'] : 0,
                'total' => (isset($item['promotion']))? ($item['price'] - ($item['price'] * ($this->promotion->discount_percentage/100))) * $item['quantity'] : $item['price'] * $item['quantity'],
            ];

            // Buscamos sub servicios por producto
            if(isset($item['services']) && count($item['services']) > 0){
                foreach ($item['services'] as $key => $sub){
                    $servideTemp = Service::find($sub['id']);
                    $itemsData[] = [
                        'product_id' => $sub['id'],
                        'product_code' => $servideTemp->code,
                        'product_name' => $servideTemp->name,
                        'price' => $sub['price'],
                        'quantity' => $sub['quantity'],
                        'unidad_medida' => $servideTemp->siat_data_medida_code,
                        'numero_seria'  => '',
                        'numero_imei'   => '',
                        'codigo_producto_sin' => $servideTemp->siat_data_product_code,
                        'codigo_actividad' => $servideTemp->siat_data_actividad_code,
                        'discount' => (isset($sub['promotion']))? $sub['price'] * ($sub['promotion']/100) * $sub['quantity'] : 0,
                        'total' => SaleItemService::calculateSubtotal($sub['quantity'], $sub['price'], $sub['promotion'])
                    ];
                }
            }

            
        }

        // 3. Prepara los datos (típicamente desde una solicitud o base de datos)
        $invoiceDataArray = [
            'customerId' => $this->customer->amyr_customer_id,
            'customer' => $this->customer->name,
            'nitRucNif' => $this->customer->nit,
            'subTotal' => $this->subtotal,
            'totalTax' => number_format($this->total * 0.13, 2),
            'discount' => number_format($this->discountAmount, 2),
            'montoGiftcard' => '0.00',
            'total' => $this->total,
            // 'invoiceDateTime' => now()->toIso8601String(),
            'invoiceDateTime' => "",
            'currencyCode' => 'BOB',
            'codigoSucursal' => $this->branch->amyrConnectionBranch->sucursal,
            'puntoVenta' => $this->branch->amyrConnectionBranch->point_sale,
            'codigoDocumentoSector' => 1,
            'tipoDocumentoIdentidad' => $this->customer->document_type,
            'codigoMetodoPago' => (strcmp($this->paymentType, SalePayment::METHOD_TRANSFER) == 0)? 7 : 1,
            'codigoMoneda' => 1,
            'complemento' => $this->customer->complement ?? '',
            'numeroTarjeta' => null,
            'tipoCambio' => 1,
            'tipoFacturaDocumento' => 1,
            'data' => '{}',
            'items' => $itemsData,
        ];
        try {
            // 4. Crea el DTO y valida los datos
            $invoiceDto = new InvoiceCreationDto($invoiceDataArray);

            // 5. Llama al servicio para crear la factura
            $response = $invoiceApiService->createInvoice($invoiceDto);

            if ($response) {
                Log::info("Factura creada con éxito en MonoInvoices", [
                    'response' => $response,
                    'code' => $response['code']
                ]);
                if($response['response'] == 'ok' && $response['code'] == 200){
                    return $response['data'];
                } else {
                    Notification::make()
                    ->title('Error')
                    ->body("No se pudo crear la factura en el sistema SIAT. " . $response['message'])
                    ->danger()
                    ->send();
                    return null;
                }
            } else {
                Log::info("Factura Error al crear la factura en MonoInvoices", [
                    'invoiceData' => $invoiceDataArray
                ]);     
                Notification::make()
                ->title('Error')
                ->body("No se pudo crear la factura en el sistema SIAT.")
                ->danger()
                ->send();
                return null;
            }
        } catch (Exception $e) {
            // Los datos de entrada no cumplen con la estructura del DTO
            // echo "Error de validación del payload: " . $e->getMessage();
            Notification::make()
                ->title('Error en validacion')
                ->body("Error de validación de la venta: " . $e->getMessage())
                ->danger()
                ->send();
            return null;
        }
         catch (ValidationException $e) {
            // Los datos de entrada no cumplen con la estructura del DTO
            // echo "Error de validación del payload: " . $e->getMessage();
            Notification::make()
                ->title('Error en validacion')
                ->body("Error de validación del payload: " . $e->getMessage())
                ->danger()
                ->send();
            return null;
        }   
    }
    
    private function voidSiatInvoice($siatInvoiceId)
    {
        $invoiceApiService = new MonoInvoiceApiService($this->branch);
        $result = $invoiceApiService->voidInvoice($siatInvoiceId);
        if($result != null && $result['response'] == 'ok' && $result['code'] == 200){
            Notification::make()
                ->title('Se anulo la factura SIAT')
                ->body("")
                ->success()
                ->send();
            return true;
        }

        Notification::make()
            ->title('Error al anular la factura SIAT')
            ->body("Error: " . $result['message'])
            ->danger()
            ->send();
        return false;
    }

    private function getCustomerSiatByNit()
    {
        $amyrCustomerApiService = new \App\Services\AmyrCustomerApiService($this->branch->amyrConnectionBranch->token);
        // $amyrCustomerApiService->withToken($this->branch->amyrConnectionBranch->token);

        $response = $amyrCustomerApiService->searchByNit($this->customer->nit);
        // dd($response);
        if($response == null){
            // $this->customer
            $customerDto = new CustomerSiatDto([
                'code' => "",
                'storeId' => $this->branch->id,
                'firstname' => "",
                'lastname' => $this->customer->name,
                'identityDocument' => $this->customer->identity_document,
                'company' => $this->customer->name,
                'phone' => $this->customer->phone,
                'email' => $this->customer->email,
                'address1' => $this->customer->address,
                'meta' => ['_nit_ruc_nif' => $this->customer->nit, '_billing_name' => null],
                
            ]);

            $response = $amyrCustomerApiService->create($customerDto);
            Log::info("Respuesta creacion cliente Amyr::: ", ['response' => $response]);
        } else {
            $customerUpdate = [
                "customer_id" => $response['customer_id'],
                'last_name' => $this->customer->name,
                'identity_document' => $this->customer->identity_document,
                'company' => $this->customer->name,
                'phone' => $this->customer->phone,
                'email' => $this->customer->email,
                'address1' => $this->customer->address,
                'meta' => ['_nit_ruc_nif' => $this->customer->nit, '_billing_name' => null],
            ];
            $response = $amyrCustomerApiService->update($customerUpdate);
        }
        $this->customer->amyr_customer_id = $response['customer_id'];
        $this->customer->save();
    }

    public function completePayment1($generateInvoice = false)
    {
        $this->validateCart();

        if ($this->message_error) {
            return;
        }

        // Intercepción para Pago QR
        if ($this->paymentType === SalePayment::METHOD_QR) {
            $this->isInvoicePending = $generateInvoice;
            $this->generateQrCode();
            return;
        }

        // dd("sssssss");
        // Flujo normal para Efectivo/Tarjeta
        $this->completePayment($generateInvoice);
    }

    /**
     * Llama al servicio EconomicoApiService para generar el QR.
     */
    public function generateQrCode()
    {
        try {
            // Instanciamos tu servicio
            $apiService = new EconomicoApiService($this->branch->id);

            // Preparamos el DTO PaymentQr usando el constructor con argumentos nombrados
            // para respetar la estructura definida en PaymentQr.php
            $paymentQr = new PaymentQr(
                amount: (float) $this->total, // El DTO espera float, no string formateado
                currency: 'BOB',
                gloss: "Compra en " . $this->branch->name,
                singleUse: 'true', // El DTO espera string 'true' o 'false', no boolean
                expirationDate: now()->addHours(24)->format('Y-m-d'),
                additionalData: [
                    'cajero' => Auth::user()->name ?? 'Sistema',
                    'sucursal' => $this->branch->name,
                    'cliente' => $this->customer->name ?? 'SN'
                ]
            );

            // Llamada al servicio
            $response = $apiService->generateQr($paymentQr->amount, $paymentQr->gloss,
                $paymentQr->currency, 
                null, 
                (strcmp($paymentQr->singleUse, "true") == 0));

            // Verificamos si obtuvimos la imagen (el DTO sugiere que puede venir como qrImage o qrBase64)
            // Asumimos que el servicio devuelve un array o el mismo DTO poblado.
            $qrImage = $response->qrImage ?? $response->qrBase64 ?? null;

            if ($qrImage) {
                $this->qrImage = $qrImage;
                $this->qrId = $response->qrId;
                $this->showQrModal = true;
            } else {
                throw new Exception("La respuesta del banco no contiene la imagen QR.");
            }

        } catch (Exception $e) {
            Notification::make()
                ->title('Error al generar QR')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Acción para cerrar el modal y limpiar el QR (cancelar operación).
     */
    public function closeQrModal()
    {
        $this->showQrModal = false;
        $this->qrImage = null;
        $this->qrId = null;
    }

    /**
     * Acción para confirmar que el pago QR fue exitoso (botón manual en el modal).
     */
    public function confirmQrPayment()
    {
        $this->closeQrModal();
        // Procedemos a guardar la venta como pagada
        $this->completePayment($this->isInvoicePending);
    }

    public function verifyQrPayment(){
        $this->qrModalMessage = "";
        try{
            $apiService = new EconomicoApiService($this->branch->id);
            $result = $apiService->checkQrStatus($this->qrId);
            Log::info("Resultado verificar pago qr:: " . json_encode($result));
            if($result['success'] && $result['estado']){
                // $this->closeQrModal();
                $this->qrModalMessage = "EXITO";
                $this->confirmQrPayment();
            } else {
                $this->qrModalMessage = !empty($result['message'])? $result['message'] : "No Se realizo el pago.";
            }
        } catch (Exception $e){
            Notification::make()
                ->title('Error al comprobar ESTADO QR')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    private function validateCart()
    {
        $this->message_error = '';
        if (empty($this->cart)) {
            $this->message_error = "El carrito está vacío.";
        }
    }

}
