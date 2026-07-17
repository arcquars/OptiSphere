<?php

declare(strict_types=1);

use App\Filament\Resources\ProductAuthentications\Pages\ListProductAuthentications;
use App\Filament\Resources\ProductAuthentications\ProductAuthenticationResource;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductAuthentication;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

// Se ejecuta contra la BD real (MySQL) envuelto en transacción con rollback: la suite
// SQLite está rota por la migración preexistente de optical_properties.
// Correr con: DB_CONNECTION=mysql DB_DATABASE=filament vendor/bin/pest ...
uses(Tests\TestCase::class);

beforeEach(function (): void {
    DB::beginTransaction();

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'branch-coordinator', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'frequent-customer', 'guard_name' => 'web']);

    $this->admin = User::factory()->create(['name' => 'Admin Test', 'is_active' => true]);
    $this->admin->assignRole('admin');

    // Cliente frecuente (customer vinculado a un user) que hizo la solicitud
    $frequentUser = User::factory()->create(['name' => 'Cliente Frecuente Test', 'is_active' => true]);
    $frequentUser->assignRole('frequent-customer');

    $this->customer = Customer::create(['name' => 'Customer Test']);
    $this->customer->user_id = $frequentUser->id;
    $this->customer->save();

    $supplier = Supplier::create(['name' => 'Proveedor Test']);
    $this->product = Product::create(['name' => 'Lente Test', 'code' => 'cod-test-1', 'supplier_id' => $supplier->id]);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($this->admin);
});

afterEach(function (): void {
    DB::rollBack();
});

/** Crea una solicitud de autenticación con el estado y fecha dados. */
function makeAuthentication(Customer $customer, Product $product, bool $approved = false, ?string $createdAt = null): ProductAuthentication
{
    $auth = ProductAuthentication::create([
        'product_id' => $product->id,
        'cliente' => 'Comprador X',
        'fecha_compra' => '2026-07-01',
        'frequent_customer_id' => $customer->id,
        'is_authentication' => $approved,
    ]);

    if ($createdAt !== null) {
        $auth->forceFill(['created_at' => $createdAt])->save();
    }

    return $auth->refresh();
}

it('aprueba con el toggle: guarda el booleano, la auditoría y notifica', function (): void {
    $auth = makeAuthentication($this->customer, $this->product, approved: false);

    Livewire::test(ListProductAuthentications::class)
        ->call('updateTableColumnState', 'is_authentication', (string) $auth->getKey(), true)
        ->assertNotified('Autenticación actualizada correctamente');

    $auth->refresh();

    expect($auth->is_authentication)->toBeTrue()
        ->and($auth->authentication_approved_date)->not->toBeNull()
        ->and($auth->authentication_approved_by)->toBe('Admin Test');
});

it('al desaprobar limpia la traza de auditoría', function (): void {
    $auth = makeAuthentication($this->customer, $this->product, approved: true);
    // Simula una aprobación previa con traza
    $auth->forceFill([
        'authentication_approved_date' => now(),
        'authentication_approved_by' => 'Admin Anterior',
    ])->save();

    Livewire::test(ListProductAuthentications::class)
        ->call('updateTableColumnState', 'is_authentication', (string) $auth->getKey(), false)
        ->assertNotified('Autenticación actualizada correctamente');

    $auth->refresh();

    expect($auth->is_authentication)->toBeFalse()
        ->and($auth->authentication_approved_date)->toBeNull()
        ->and($auth->authentication_approved_by)->toBeNull();
});

it('ordena pendientes primero y, dentro de ellas, las más recientes arriba', function (): void {
    $aprobada = makeAuthentication($this->customer, $this->product, approved: true, createdAt: '2026-07-16 10:00:00');
    $pendienteVieja = makeAuthentication($this->customer, $this->product, approved: false, createdAt: '2026-07-14 10:00:00');
    $pendienteNueva = makeAuthentication($this->customer, $this->product, approved: false, createdAt: '2026-07-15 10:00:00');

    Livewire::test(ListProductAuthentications::class)
        ->assertCanSeeTableRecords([$pendienteNueva, $pendienteVieja, $aprobada], inOrder: true);
});

it('solo el rol admin puede acceder al recurso', function (): void {
    expect(ProductAuthenticationResource::canAccess())->toBeTrue();

    $coordinator = User::factory()->create(['is_active' => true]);
    $coordinator->assignRole('branch-coordinator');
    $this->actingAs($coordinator);

    expect(ProductAuthenticationResource::canAccess())->toBeFalse();
});

it('el recurso es de solo lectura y aprobación', function (): void {
    $auth = makeAuthentication($this->customer, $this->product);

    expect(ProductAuthenticationResource::canCreate())->toBeFalse()
        ->and(ProductAuthenticationResource::canEdit($auth))->toBeFalse()
        ->and(ProductAuthenticationResource::canDelete($auth))->toBeFalse()
        ->and(ProductAuthenticationResource::canDeleteAny())->toBeFalse()
        ->and(array_keys(ProductAuthenticationResource::getPages()))->toBe(['index']);
});
