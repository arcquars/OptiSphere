<?php

declare(strict_types=1);

use App\Filament\FrequentCustomer\Resources\SaleHistory\Pages\ListSaleHistory;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

// Se ejecuta contra la BD real (MySQL) envuelto en una transacción con rollback,
// evitando el SQLite de la suite (una migración de optical_properties no es compatible)
// y sin dejar datos residuales. Se corre con: DB_CONNECTION=mysql vendor/bin/pest ...
uses(Tests\TestCase::class);

beforeEach(function (): void {
    DB::beginTransaction();

    Role::firstOrCreate(['name' => 'frequent-customer', 'guard_name' => 'web']);

    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('frequent-customer');

    $this->customer = Customer::create(['name' => 'Cliente Frecuente Test']);
    $this->customer->user_id = $this->user->id;
    $this->customer->save();

    // Una compra para que el historial tenga al menos una fila
    $supplier = Supplier::create(['name' => 'Proveedor Test']);
    $product = Product::create(['name' => 'Lente Test', 'code' => 'cod-test-1', 'supplier_id' => $supplier->id]);
    $branch = Branch::create(['name' => 'Sucursal Test', 'address' => 'Calle 1']);

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
        'salable_id' => $product->id,
        'salable_type' => Product::class,
        'quantity' => 2,
        'base_price' => 10,
        'final_price_per_unit' => 10,
        'subtotal' => 20,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('frequent-customer'));
    $this->actingAs($this->user);
});

afterEach(function (): void {
    DB::rollBack();
});

it('muestra "Autentificar Producto" como acción de cabecera (un solo botón)', function (): void {
    Livewire::test(ListSaleHistory::class)
        ->assertTableHeaderActionsExistInOrder(['autentificar_producto']);
});

it('mantiene la acción Ver en la fila del historial', function (): void {
    $sale = Sale::where('customer_id', $this->customer->id)->firstOrFail();

    // La acción de autenticar ya no es de fila; se probó como header action arriba.
    // Aquí solo se confirma que la fila conserva "Ver".
    Livewire::test(ListSaleHistory::class)
        ->assertTableActionExists('view', record: $sale);
});
