<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\InventoryMovement;
use App\Models\Price;
use App\Models\SalePayment;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Exception;

class SaleService
{
    /**
     * @var CreditService
     */
    protected $creditService;

    public function __construct(CreditService $creditService)
    {
        $this->creditService = $creditService;
    }

    /**
     * Procesa la creación de una nueva venta al contado (contado).
     *
     * @param array $data Datos de la venta (customer_id, branch_id, user_id, items, payment_method, etc.)
     * @return Sale La venta recién creada.
     * @throws InvalidArgumentException Si falla la validación del inventario o del precio.
     * @throws Exception Si la transacción de base de datos falla.
     */
    public function createSale(array $data): Sale
    {
        // 1. Validar y preparar los datos de la venta (items)
        $processedItems = $this->processAndValidateItems($data['items'], $data['branch_id'], $data['customer_id']);

        // 2. Calcular totales
        $totals = $this->calculateTotals($processedItems);

        // 3. Determinar el estado y tipo de la venta
        $isCredit = $data['status'] === Sale::SALE_STATUS_PARTIAL_PAYMENT;

        if ($isCredit) {
            // Redirigir la lógica a createCreditSale si es a crédito
            return $this->createCreditSale($data, $processedItems, $totals);
        }

        // Si es al contado, el saldo pagado es el total y el estado es PAGADO.
        $status = Sale::SALE_STATUS_PAID;
        $paidAmount = $totals['total_amount'];
        $dueAmount = 0.00;

        // 4. Ejecutar la transacción de la venta
        return DB::transaction(function () use ($data, $processedItems, $totals, $status, $paidAmount, $dueAmount) {

            // a) Creación de la venta (Sale)
            $sale = Sale::create([
                'customer_id' => $data['customer_id'],
                'branch_id' => $data['branch_id'],
                'user_id' => $data['user_id'],
                'total_amount' => $totals['total_amount'],
                'final_total' => $data['final_total'],
                'final_discount' => $data['final_discount'],
                'status' => $status,
                'payment_method' => $data['payment_method'],
                'sale_type' => $data['sale_type'],
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            // b) Adjuntar ítems de la venta (Pivot table sale_item)
            $this->attachSaleItems($sale, $processedItems);

            // c) Gestión de inventario (solo para productos)
            $this->deductInventory($data['branch_id'], $processedItems, $sale->id, $data['user_id']);

            // d) Registrar el pago único si es al contado
            SalePayment::create([
                'sale_id' => $sale->id,
                'user_id' => $data['user_id'],
                'branch_id' => $data['branch_id'],
                'amount' => $data['final_total'],
                'payment_method' => $data['payment_method'], // EFECTIVO, TARJETA, etc.
                'notes' => 'Pago completo al contado registrado en la venta',
            ]);

            return $sale;
        });
    }

    /**
     * Procesa la creación de una nueva venta a crédito (sin abono inicial).
     *
     * @param array $data Datos de la venta
     * @param array $processedItems Items validados
     * @param array $totals Totales calculados
     * @return Sale La venta a crédito recién creada.
     * @throws InvalidArgumentException Si el cliente no puede comprar a crédito.
     */
    protected function createCreditSale(array $data, array $processedItems, array $totals): Sale
    {
        $customer = Customer::find($data['customer_id']);

        if (!$customer || !$customer->can_buy_on_credit) {
            throw new InvalidArgumentException("El cliente seleccionado no está autorizado para compras a crédito.");
        }

        // Estado inicial: CRÉDITO
        $status = Sale::SALE_STATUS_CREDIT;
        $paidAmount = $data['paid_amount'] ?? 0.00; // Podría haber un abono inicial
        $dueAmount = $data['due_amount'];

        // La validación del abono inicial la hacemos aquí
        if ($paidAmount < 0 || $paidAmount > $totals['total_amount']) {
            throw new InvalidArgumentException("El abono inicial es inválido.");
        }


        return DB::transaction(function () use ($data, $processedItems, $totals, $status, $paidAmount, $dueAmount) {

            // a) Creación de la venta (Sale)
            $sale = Sale::create([
                'customer_id' => $data['customer_id'],
                'branch_id' => $data['branch_id'],
                'user_id' => $data['user_id'],
                'total_amount' => $totals['total_amount'],
                'final_total' => $data['final_total'],
                'final_discount' => $data['final_discount'],
                'status' => $status,
                'payment_method' => $data['payment_method'],
                'sale_type' => $data['sale_type'],
                'paid_amount' => 0,
                'due_amount' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            // b) Adjuntar ítems de la venta
            $this->attachSaleItems($sale, $processedItems);

            // c) Gestión de inventario (solo para productos)
            $this->deductInventory($data['branch_id'], $processedItems, $sale->id, $data['user_id']);

            // d) Registrar el abono inicial si existe
            if ($paidAmount > 0) {
                // Utilizamos el CreditService para registrar el primer abono.
                $this->creditService->registerPayment(
                    $sale,
                    $paidAmount,
                    $data['payment_method'], // Método del abono inicial
                    $data['user_id'],
                    'Abono inicial de la venta a crédito'
                );
            }

            return $sale;
        });
    }

    /**
     * Valida la existencia de los productos y precios, y retorna los ítems enriquecidos.
     *
     * @param array $items
     * @param int $branchId
     * @param int $customerId
     * @return array
     * @throws InvalidArgumentException
     */
    protected function processAndValidateItems(array $items, int $branchId, int $customerId): array
    {
        $processedItems = [];
        $customer = Customer::find($customerId);
        $priceType = $customer ? $customer->type : Price::TYPE_NORMAL;

        foreach ($items as $item) {
            $model = $item['salable_type'] === 'product'
                ? Product::find($item['salable_id'])
                : \App\Models\Service::find($item['salable_id']);

            if (!$model) {
                throw new InvalidArgumentException("Ítem no encontrado (ID: {$item['salable_id']}, Tipo: {$item['salable_type']}).");
            }

            $quantity = (int) $item['quantity'];
            if ($quantity <= 0) {
                throw new InvalidArgumentException("La cantidad para {$model->name} debe ser positiva.");
            }

            // 1. Validar Stock (solo para productos)
            if ($item['salable_type'] === 'product') {
                $stock = ProductStock::where('product_id', $model->id)
                    ->where('branch_id', $branchId)
                    ->first();
                $availableQuantity = $stock ? $stock->quantity : 0;

                if ($quantity > $availableQuantity) {
                    throw new InvalidArgumentException("Stock insuficiente para {$model->name}. Disponible: {$availableQuantity}.");
                }
            }

            // 2. Obtener Precio y calcular subtotal/descuento
            $basePrice = $model->getPriceByType($branchId, $priceType);
            $pricePerUnit = $item['base_price'] ?? $basePrice; // Permite anular el precio si se envía

            if ($pricePerUnit <= 0) {
                throw new InvalidArgumentException("Precio no encontrado o inválido para {$model->name}.");
            }

            $promotion_id = $item['promotion_id'] ?? null;
            $promotion_discount_rate = $item['promotion_discount_rate'];
            $final_price_per_unit = $item['final_price_per_unit'];
            $subtotal = $item['subtotal'];

            $processedItems[] = [
                'model' => $model,
                'type' => $item['salable_type'],
                'id' => $model->id,
                'quantity' => $quantity,
                'price_per_unit' => $pricePerUnit,
                'subtotal' => $subtotal,
                'promotion_id' => $promotion_id,
                'promotion_discount_rate' => $promotion_discount_rate,
                'final_price_per_unit' => $final_price_per_unit,
            ];
        }

        return $processedItems;
    }

    /**
     * Calcula los totales de la venta.
     *
     * @param array $items Ítems procesados.
     * @return array
     */
    protected function calculateTotals(array $items): array
    {
        $subtotalAmount = array_sum(array_column($items, 'subtotal'));
        $totalDiscount = array_sum(array_column($items, 'discount'));
        $totalAmount = $subtotalAmount - $totalDiscount;

        return [
            'subtotal_amount' => $subtotalAmount,
            'total_discount' => $totalDiscount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Adjunta los ítems de la venta a la tabla pivot `sale_item`.
     *
     * @param Sale $sale
     * @param array $processedItems
     * @return void
     */
    protected function attachSaleItems(Sale $sale, array $processedItems): void
    {
        $saleItems = [];
//        'model' => $model,
//                'type' => $item['salable_type'],
//                'id' => $model->id,
//                'quantity' => $quantity,
//                'price_per_unit' => $pricePerUnit,
//                'subtotal' => $subtotal,
//                'promotion_id' => $promotion_id,
//                'promotion_discount_rate' => $promotion_discount_rate,
//                'final_price_per_unit' => $final_price_per_unit,
        foreach ($processedItems as $item) {
            $saleItems[] = [
                // Estas son las columnas de la tabla 'sale_items' (excepto 'sale_id')
                'salable_id' => $item['id'], // El ID del Producto o Servicio
                'salable_type' => $item['type'] === 'product' ? Product::class : Service::class,
                'quantity' => $item['quantity'],
                'base_price' => $item['price_per_unit'],
                // Asegúrate de incluir todos los campos obligatorios/fillable de SaleItem
                'promotion_id' => $item['promotion_id'] ?? null,
                'promotion_discount_rate' => $item['promotion_discount_rate'] ?? 0.0,
                'final_price_per_unit' => $item['final_price_per_unit'],
                'subtotal' => $item['subtotal'],
                'created_at' => now(), // Agrega timestamps si no son automáticos
                'updated_at' => now(),
            ];
        }
        $sale->items()->createMany($saleItems); // Asume que la relación items usa la tabla pivot sale_item
    }

    /**
     * Deduce la cantidad vendida del inventario de la sucursal y registra el movimiento.
     *
     * @param int $branchId
     * @param array $processedItems
     * @param int $saleId
     * @param int $userId
     * @return void
     */
    protected function deductInventory(int $branchId, array $processedItems, int $saleId, int $userId): void
    {
        foreach ($processedItems as $item) {
            if ($item['type'] !== 'product') {
                continue;
            }

            /** @var Product $product */
            $product = $item['model'];
            $quantityToDeduct = $item['quantity'];

            // 1. Obtener y actualizar ProductStock
            $productStock = ProductStock::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->first();

            if (!$productStock) {
                // Esto no debería pasar si la validación es correcta, pero es una buena práctica
                throw new Exception("Stock no encontrado para el producto ID: {$product->id} en la sucursal ID: {$branchId}.");
            }

            $oldQuantity = $productStock->quantity;
            $newQuantity = $oldQuantity - $quantityToDeduct;

            $productStock->update(['quantity' => $newQuantity]);

            // 2. Registrar el movimiento de inventario por la venta
            InventoryMovement::create([
                'product_id' => $product->id,
                'from_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                'from_location_id' => $branchId,
                'to_location_type' => null, // La venta es el destino final
                'to_location_id' => null,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'difference' => -$quantityToDeduct, // La diferencia es negativa (salida)
                'type' => 'VENTA',
                'description' => "Venta registrada, movimiento de salida de la sucursal {$branchId}. Venta ID: {$saleId}",
                'user_id' => $userId,
            ]);
        }
    }
}
