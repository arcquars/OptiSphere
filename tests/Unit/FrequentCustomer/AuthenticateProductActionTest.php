<?php

declare(strict_types=1);

use App\Filament\FrequentCustomer\Resources\SaleHistory\Pages\ListSaleHistory;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductAuthentication;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

// Reproduce el bug reportado: se corre contra la BD real (MySQL) en transacción con rollback,
// disparando la acción de cabecera "autentificar_producto" tal como la ejecuta el usuario en
// el navegador (no solo el Service directamente), para verificar que al agotar el tope de
// unidades compradas la notificación de error se muestra y no se crea el registro extra.
uses(Tests\TestCase::class);

beforeEach(function (): void {
    DB::beginTransaction();

    Role::firstOrCreate(['name' => 'frequent-customer', 'guard_name' => 'web']);

    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('frequent-customer');

    $this->customer = Customer::create(['name' => 'Cliente Frecuente Test']);
    $this->customer->user_id = $this->user->id;
    $this->customer->save();

    $supplier = Supplier::create(['name' => 'Proveedor Test']);
    $this->product = Product::create(['name' => 'Lente Test', 'code' => 'cod-test-1', 'supplier_id' => $supplier->id]);
    $branch = Branch::create(['name' => 'Sucursal Test', 'address' => 'Calle 1']);

    // El cliente compró exactamente 1 unidad del producto
    $sale = Sale::create([
        'branch_id' => $branch->id,
        'customer_id' => $this->customer->id,
        'user_id' => $this->user->id,
        'total_amount' => 100,
        'final_total' => 100,
        'due_amount' => 0,
        'payment_method' => 'efectivo',
        'status' => Sale::SALE_STATUS_PAID,
        'date_sale' => '2026-07-15',
    ]);
    SaleItem::create([
        'sale_id' => $sale->id,
        'salable_id' => $this->product->id,
        'salable_type' => Product::class,
        'quantity' => 1,
        'base_price' => 10,
        'final_price_per_unit' => 10,
        'subtotal' => 10,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('frequent-customer'));
    $this->actingAs($this->user);
});

afterEach(function (): void {
    DB::rollBack();
});

it('permite autenticar dentro del tope comprado y notifica éxito', function (): void {
    Livewire::test(ListSaleHistory::class)
        ->callTableAction('autentificar_producto', data: [
            'product_id' => $this->product->id,
            'cliente' => 'Juan Perez',
            'fecha_compra' => '2026-07-15',
        ])
        ->assertNotified('Producto autenticado');

    expect(ProductAuthentication::where('product_id', $this->product->id)->count())->toBe(1);
});

it('al superar el tope comprado, notifica el error y NO crea el registro extra', function (): void {
    // Ya se autenticó la única unidad comprada
    ProductAuthentication::create([
        'product_id' => $this->product->id,
        'cliente' => 'Primera Autenticación',
        'fecha_compra' => '2026-07-15',
        'frequent_customer_id' => $this->customer->id,
    ]);

    Livewire::test(ListSaleHistory::class)
        ->callTableAction('autentificar_producto', data: [
            'product_id' => $this->product->id,
            'cliente' => 'Segundo Intento',
            'fecha_compra' => '2026-07-15',
        ])
        ->assertNotified('Ya autenticó 1 de 1 unidades compradas de este producto. No puede registrar más.');

    // Sigue habiendo solo 1 registro: el segundo intento no se persistió
    expect(ProductAuthentication::where('product_id', $this->product->id)->count())->toBe(1);
});
