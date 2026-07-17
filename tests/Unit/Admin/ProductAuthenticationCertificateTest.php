<?php

declare(strict_types=1);

use App\Filament\Resources\ProductAuthentications\Pages\ListProductAuthentications;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductAuthentication;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ProductAuthenticationService;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

// Corre en MySQL (filament_testing, ver phpunit.xml/.env.testing) con transacción y
// rollback como capa extra de seguridad. Basta con `vendor/bin/pest` — no requiere
// overrides manuales de DB_* (esos apuntaban por error a la BD real; nunca los repitas).
uses(Tests\TestCase::class);

beforeEach(function (): void {
    DB::beginTransaction();

    $supplier = Supplier::create(['name' => 'Proveedor Cert Test']);
    $this->product = Product::create([
        'name' => 'Lente Cert Test',
        'code' => 'cod-cert-1',
        'supplier_id' => $supplier->id,
    ]);
    $this->customer = Customer::create(['name' => 'Customer Cert Test']);
    $this->service = app(ProductAuthenticationService::class);
});

afterEach(function (): void {
    DB::rollBack();
});

function makeCert(Customer $customer, Product $product, bool $approved): ProductAuthentication
{
    return ProductAuthentication::create([
        'product_id' => $product->id,
        'cliente' => 'Comprador Certificado',
        'fecha_compra' => '2026-07-01',
        'frequent_customer_id' => $customer->id,
        'is_authentication' => $approved,
        'authentication_approved_date' => $approved ? now() : null,
        'authentication_approved_by' => $approved ? 'Admin Test' : null,
    ]);
}

it('genera un token URL-safe que el controlador puede desencriptar (round-trip)', function (): void {
    $auth = makeCert($this->customer, $this->product, approved: true);

    $url = $this->service->buildPublicUrl($auth);
    $token = basename(parse_url($url, PHP_URL_PATH));

    expect($token)->not->toContain('+')
        ->and($token)->not->toContain('/');

    // El controlador revierte el reemplazo y desencripta al ID original
    $decoded = Crypt::decrypt(str_replace(['-', '_'], ['+', '/'], $token));
    expect($decoded)->toBe($auth->id);
});

it('la URL pública abre el certificado (200) sin login y muestra los datos reales', function (): void {
    $auth = makeCert($this->customer, $this->product, approved: true);

    $response = $this->get($this->service->buildPublicUrl($auth));

    $response->assertOk()
        ->assertSee('Lente Cert Test')
        ->assertSee('cod-cert-1')
        ->assertSee('Comprador Certificado')
        ->assertSee('¡Producto Auténtico Verificado!', escape: false);
});

it('devuelve 403 si la autenticación no está aprobada', function (): void {
    $auth = makeCert($this->customer, $this->product, approved: false);

    $this->get($this->service->buildPublicUrl($auth))->assertForbidden();
});

it('devuelve 404 con un token inválido', function (): void {
    $this->get(route('product.authentication', ['token' => 'token-basura-invalido']))
        ->assertNotFound();
});

it('la columna enlaza solo en las filas aprobadas', function (): void {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('admin');

    $aprobada = makeCert($this->customer, $this->product, approved: true);
    $pendiente = makeCert($this->customer, $this->product, approved: false);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($admin);

    Livewire::test(ListProductAuthentications::class)
        // La fila aprobada muestra el enlace; la pendiente cae al placeholder
        ->assertTableColumnStateSet('ver_autentificacion', 'Ver autentificación', record: $aprobada)
        ->assertTableColumnStateSet('ver_autentificacion', null, record: $pendiente);
});
