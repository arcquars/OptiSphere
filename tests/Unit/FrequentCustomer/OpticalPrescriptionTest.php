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

// Corre en MySQL (filament_testing) con transacción y rollback.
uses(Tests\TestCase::class);

beforeEach(function (): void {
    DB::beginTransaction();

    Role::firstOrCreate(['name' => 'frequent-customer', 'guard_name' => 'web']);

    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('frequent-customer');

    $this->customer = Customer::create(['name' => 'Cliente Receta Test']);
    $this->customer->user_id = $this->user->id;
    $this->customer->save();

    $supplier = Supplier::create(['name' => 'Proveedor Receta Test']);
    $this->product = Product::create([
        'name' => 'Lente Receta Test',
        'code' => 'cod-receta-1',
        'supplier_id' => $supplier->id,
    ]);
    $branch = Branch::create(['name' => 'Sucursal Receta', 'address' => 'Calle 1']);

    // El cliente compró 5 unidades: margen suficiente para varias autenticaciones
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
        'quantity' => 5,
        'base_price' => 10,
        'final_price_per_unit' => 10,
        'subtotal' => 50,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('frequent-customer'));
    $this->actingAs($this->user);
});

afterEach(function (): void {
    DB::rollBack();
});

it('persiste los datos de la receta óptica al autentificar', function (): void {
    Livewire::test(ListSaleHistory::class)
        ->callTableAction('autentificar_producto', data: [
            'product_id' => $this->product->id,
            'cliente' => 'Juan Perez',
            'fecha_compra' => '2026-07-15',
            'od_sphere' => -1.25,
            'od_cylinder' => -0.5,
            'od_axis' => 180,
            'oi_sphere' => -1.75,
            'oi_cylinder' => -0.25,
            'oi_axis' => 90,
            'add' => 2.25,
            'dip' => 62.5,
        ])
        ->assertNotified('Producto autenticado');

    $auth = ProductAuthentication::where('product_id', $this->product->id)->firstOrFail();

    expect($auth->od_sphere)->toBe(-1.25)
        ->and($auth->od_cylinder)->toBe(-0.5)
        ->and($auth->od_axis)->toBe(180)
        ->and($auth->oi_sphere)->toBe(-1.75)
        ->and($auth->oi_cylinder)->toBe(-0.25)
        ->and($auth->oi_axis)->toBe(90)
        ->and($auth->add)->toBe(2.25)
        ->and($auth->dip)->toBe(62.5);
});

it('permite autentificar sin receta óptica dejando los campos en null', function (): void {
    Livewire::test(ListSaleHistory::class)
        ->callTableAction('autentificar_producto', data: [
            'product_id' => $this->product->id,
            'cliente' => 'Maria Lopez',
            'fecha_compra' => '2026-07-15',
        ])
        ->assertNotified('Producto autenticado');

    $auth = ProductAuthentication::where('product_id', $this->product->id)->firstOrFail();

    expect($auth->od_sphere)->toBeNull()
        ->and($auth->od_cylinder)->toBeNull()
        ->and($auth->od_axis)->toBeNull()
        ->and($auth->oi_sphere)->toBeNull()
        ->and($auth->oi_cylinder)->toBeNull()
        ->and($auth->oi_axis)->toBeNull()
        ->and($auth->add)->toBeNull()
        ->and($auth->dip)->toBeNull()
        // Los campos existentes siguen guardándose
        ->and($auth->cliente)->toBe('Maria Lopez');
});
