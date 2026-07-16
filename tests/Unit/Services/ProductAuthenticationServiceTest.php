<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductAuthentication;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ProductAuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

// Los tests de este Service necesitan base de datos: se enlazan TestCase + RefreshDatabase
// (por convención Pest solo los aplica a Feature, aquí se hace explícito).
uses(Tests\TestCase::class, RefreshDatabase::class);

/**
 * Registra una venta de un producto para un cliente, con la cantidad y estado dados.
 */
function createProductPurchase(Customer $customer, Product $product, float $quantity, string $status = Sale::SALE_STATUS_PAID): void
{
    $branch = Branch::create(['name' => 'Sucursal', 'address' => 'Calle 1']);
    $seller = User::factory()->create();

    $sale = Sale::create([
        'branch_id' => $branch->id,
        'customer_id' => $customer->id,
        'user_id' => $seller->id,
        'total_amount' => 100,
        'final_total' => 100,
        'due_amount' => 0,
        'payment_method' => 'efectivo',
        'status' => $status,
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'salable_id' => $product->id,
        'salable_type' => Product::class,
        'quantity' => $quantity,
        'base_price' => 10,
        'final_price_per_unit' => 10,
        'subtotal' => 10 * $quantity,
    ]);
}

beforeEach(function (): void {
    $this->service = app(ProductAuthenticationService::class);
    $this->supplier = Supplier::create(['name' => 'Proveedor']);
    $this->customer = Customer::create(['name' => 'Cliente Frecuente']);
    $this->product = Product::create([
        'name' => 'Lente',
        'code' => 'cod-100',
        'supplier_id' => $this->supplier->id,
    ]);
});

it('suma la cantidad comprada de un producto a través de varias ventas', function (): void {
    createProductPurchase($this->customer, $this->product, 3);
    createProductPurchase($this->customer, $this->product, 2);

    expect($this->service->purchasedQuantity($this->customer->id, $this->product->id))
        ->toBe(5.0);
});

it('excluye las ventas anuladas al contar la cantidad comprada', function (): void {
    createProductPurchase($this->customer, $this->product, 4);
    createProductPurchase($this->customer, $this->product, 6, Sale::SALE_STATUS_VOIDED);

    expect($this->service->purchasedQuantity($this->customer->id, $this->product->id))
        ->toBe(4.0);
});

it('arma las opciones del buscador solo con productos comprados y su cantidad', function (): void {
    createProductPurchase($this->customer, $this->product, 10);

    // Producto no comprado: no debe aparecer en las opciones
    $otro = Product::create(['name' => 'Armazón', 'code' => 'cod-999', 'supplier_id' => $this->supplier->id]);

    $options = $this->service->purchasedProductOptions($this->customer->id);

    expect($options)
        ->toHaveKey($this->product->id)
        ->and($options[$this->product->id])->toBe('Código: cod-100 - Lente (Comprados: 10)')
        ->and($options)->not->toHaveKey($otro->id);
});

it('registra la autenticación cuando no supera la cantidad comprada', function (): void {
    createProductPurchase($this->customer, $this->product, 2);

    $auth = $this->service->authenticate($this->customer->id, [
        'product_id' => $this->product->id,
        'cliente' => 'Juan Perez',
        'fecha_compra' => '2026-07-15',
    ]);

    expect($auth)->toBeInstanceOf(ProductAuthentication::class);

    $this->assertDatabaseHas('product_authentications', [
        'product_id' => $this->product->id,
        'frequent_customer_id' => $this->customer->id,
        'cliente' => 'Juan Perez',
    ]);
});

it('impide autenticar más unidades de las compradas', function (): void {
    // El cliente compró 2 unidades
    createProductPurchase($this->customer, $this->product, 2);

    $payload = [
        'product_id' => $this->product->id,
        'cliente' => 'Juan Perez',
        'fecha_compra' => '2026-07-15',
    ];

    // Las 2 primeras autenticaciones son válidas
    $this->service->authenticate($this->customer->id, $payload);
    $this->service->authenticate($this->customer->id, $payload);

    // La tercera debe fallar con error de validación en 'product_id'
    try {
        $this->service->authenticate($this->customer->id, $payload);
        $this->fail('Se esperaba una ValidationException al superar el tope.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('product_id');
    }

    // No se persistió el tercer registro
    expect(ProductAuthentication::query()
        ->where('product_id', $this->product->id)
        ->where('frequent_customer_id', $this->customer->id)
        ->count())->toBe(2);
});
