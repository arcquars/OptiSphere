<?php

declare(strict_types=1);

use App\Filament\Resources\ProductAuthentications\Pages\ListProductAuthentications;
use App\Filament\Resources\ProductAuthentications\Tables\ProductAuthenticationsTable;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductAuthentication;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Corre en MySQL (filament_testing) con transacción y rollback. La plantilla y la
// fuente se leen del filesystem real (public/), independientes de la BD.
uses(Tests\TestCase::class);

beforeEach(function (): void {
    DB::beginTransaction();

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('admin');

    $supplier = Supplier::create(['name' => 'Proveedor Cert Test']);
    $this->product = Product::create([
        'name' => 'Lente Cert Test',
        'code' => 'cod-cert-1',
        'supplier_id' => $supplier->id,
    ]);
    $this->customer = Customer::create(['name' => 'Customer Cert Test']);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($this->admin);
});

afterEach(function (): void {
    DB::rollBack();
});

function makeAuth(Customer $customer, Product $product, bool $approved): ProductAuthentication
{
    return ProductAuthentication::create([
        'product_id' => $product->id,
        'cliente' => 'Comprador Certificado',
        'fecha_compra' => '2026-07-01',
        'frequent_customer_id' => $customer->id,
        'is_authentication' => $approved,
    ]);
}

it('genera un PNG válido de las dimensiones de la plantilla con los datos dibujados', function (): void {
    $auth = makeAuth($this->customer, $this->product, approved: true);

    // Ejercita la lógica de generación y captura los bytes emitidos
    $method = new ReflectionMethod(ProductAuthenticationsTable::class, 'generarCertificado');
    $method->setAccessible(true);
    $response = $method->invoke(null, $auth->load('product'));

    expect($response)->toBeInstanceOf(StreamedResponse::class);

    ob_start();
    $response->sendContent();
    $png = (string) ob_get_clean();

    $info = getimagesizefromstring($png);

    expect($info)->not->toBeFalse()
        ->and($info[0])->toBe(1521)   // ancho de la plantilla
        ->and($info[1])->toBe(1034)   // alto de la plantilla
        ->and($info['mime'])->toBe('image/png')
        // El resultado no debe ser idéntico a la plantilla vacía (se dibujó texto + QR)
        ->and(strlen($png))->not->toBe(filesize(public_path('img/certification_template.png')));
});

it('la acción de descarga solo está visible en filas aprobadas', function (): void {
    $aprobada = makeAuth($this->customer, $this->product, approved: true);
    $pendiente = makeAuth($this->customer, $this->product, approved: false);

    Livewire::test(ListProductAuthentications::class)
        ->assertTableActionVisible('descargar_certificado', record: $aprobada)
        ->assertTableActionHidden('descargar_certificado', record: $pendiente);
});

it('al ejecutar la acción se dispara la descarga del certificado', function (): void {
    $auth = makeAuth($this->customer, $this->product, approved: true);

    Livewire::test(ListProductAuthentications::class)
        ->callTableAction('descargar_certificado', record: $auth)
        ->assertFileDownloaded('certificado-' . $auth->id . '.png');
});
