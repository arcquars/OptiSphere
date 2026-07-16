# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

> El resto de este documento está en español — es la convención del proyecto (ver sección 2) y aplica también a esta guía.

---

## 0. Qué es este proyecto

Cerisier S.R.L. — sistema de punto de venta, inventario y facturación para una óptica en Bolivia. Gestiona sucursales/almacenes, ventas, crédito, cierres de caja, y se integra con el sistema de facturación electrónica SIAT de Bolivia.

## 1. Stack tecnológico (versiones exactas)

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Runtime | PHP | ^8.2 |
| Framework | Laravel | ^12.0 |
| Panel admin | Filament | ^4.0 |
| UI reactiva | Livewire + Volt | ^3 + ^1.7 |
| CSS | Tailwind CSS | ^4.1 |
| Componentes UI | DaisyUI | ^5.0 |
| Iconos | FontAwesome (blade-fontawesome) | ^2.9 |
| Autenticación | Laravel Sanctum | ^4.0 |
| Permisos | Spatie Laravel Permission | ^6.21 |
| PDF | Barryvdh DomPDF | ^3.1 |
| Excel | Maatwebsite Excel | ^3.1 |
| Tests | Pest PHP | ^3.8 |
| Contenedor | Docker (compose) | — |

---

## 2. Convenciones de idioma — OBLIGATORIAS

```
CÓDIGO       → inglés  (clases, métodos, propiedades, variables, rutas, tablas, columnas, enums)
COMENTARIOS  → español (bloques PHPDoc, comentarios inline, mensajes de log)
```

**Ejemplos correctos:**

```php
// Obtiene todos los pedidos activos del cliente
public function getActiveOrders(Customer $customer): Collection { ... }

/** @var string $paymentStatus Estado actual del pago */
protected string $paymentStatus;
```

**Nunca mezclar:** no escribir `$clienteActivo` ni `// get active clients`.

---

## 3. Arquitectura — Patrón Services (obligatorio para DB)

Toda operación que toque la base de datos **debe pasar por un Service**. Los controladores, componentes Livewire y Resources de Filament son orquestadores; la lógica de negocio y las transacciones viven en `app/Services/`.

### Estructura de carpetas

```
app/
├── Filament/
│   ├── Resources/          # Solo definición de formularios, tablas y acciones
│   └── Widgets/
├── Http/
│   └── Controllers/        # Solo reciben request y delegan al Service
├── Livewire/               # Solo estado de UI y llamadas al Service
├── Models/                 # Eloquent puro: relaciones, scopes, casts
├── Services/               # TODA la lógica de negocio y transacciones DB
│   ├── OrderService.php
│   ├── ProductService.php
│   └── ...
├── Actions/                # Acciones de un solo propósito (opcional, para Filament)
└── Enums/                  # PHP 8 Backed Enums
```

### Plantilla base de un Service

```php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class OrderService
{
    // Dependencias inyectadas por constructor
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    /**
     * Crea una nueva orden y descuenta el stock correspondiente.
     *
     * @throws \Throwable si la transacción falla
     */
    public function createOrder(array $data, int $userId): Order
    {
        return DB::transaction(function () use ($data, $userId): Order {
            // 1. Crear la orden
            $order = Order::create([...]);

            // 2. Descontar stock por cada línea
            foreach ($data['items'] as $item) {
                $this->productService->decrementStock(
                    $item['product_id'],
                    $item['quantity']
                );
            }

            return $order->load('items');
        });
    }
}
```

### Reglas del patrón

- `DB::transaction()` **siempre** cuando la operación involucre más de una tabla.
- El Service **lanza** la excepción; el controlador/componente la **captura**.
- Los Services se **inyectan por constructor** (Laravel IoC los resuelve automáticamente).
- Un Service **puede** llamar a otro Service; **nunca** llama a un controlador.

### Carpetas adicionales (soporte de integraciones externas)

```
app/
├── DAOs/ + Interfaces/   # Data-access objects para el catálogo SIAT (DocumentoTipo, ProductoServicio, UnidadMedida), cada uno tras una interfaz
├── DTOs/                 # Objetos de transferencia hacia/desde APIs externas (SIAT, AMYR, pagos QR)
├── Contracts/             # Interfaces a nivel de app (p.ej. SalableInterface para modelos vendibles)
├── Observers/             # Observers de modelo (p.ej. WarehouseStockHistoryObserver)
├── Traits/                # Comportamiento compartido de modelos (HasPricesAndPromotions, HasPricesByBranch)
└── Helpers/               # Clases estáticas de apoyo (generación de productos, validación SIAT)
```

### 3.1 Paneles Filament (multi-panel, por rol)

Se registran tres paneles en `app/Providers/Filament/`, cada uno con su propio path y raíz de descubrimiento de resources/pages:

- `AdminPanelProvider` → `/admin`, resources/pages en `app/Filament/Resources` y `app/Filament/Pages`
- `BranchManagerPanelProvider` → `/branch-manager`, resources/pages propios en `app/Filament/BranchManager/*`
- `BranchCoordinatorPanelProvider` → `/branch-coordinator`, **reutiliza** varios de los Resources de Admin (`BranchResource`, `CustomerResource`, `ProductResource`, etc. de `app/Filament/Resources`) y registra explícitamente páginas de `app/Filament/Pages` (`AccountsReceivableReport`, `CreditPaymentResource`, `IncomeBranchsReport`) vía `->pages([...])`; el discovery propio en `app/Filament/BranchCoordinator/*` existe pero está vacío hoy — no asumir que un archivo nuevo ahí se registra solo sin agregarlo también a `->pages()`/`->resources()`.

Roles (Spatie, sembrados en `database/seeders/RoleSeeder.php`): `admin`, `accountant`, `branch-manager`, `branch-coordinator`. La ruta raíz (`routes/web.php`) redirige a un usuario autenticado a `/{nombre-de-rol-con-guiones-bajos}` según su primer rol, resuelto contra los IDs de panel registrados. `App\Http\Middleware\RedirectIfNoPanelAccess` fuerza logout si el usuario cae en un panel al que su rol no tiene acceso (`User::canAccessPanel()`). `App\Http\Middleware\BlockApiUsersFromWeb` cierra la sesión web de cualquier usuario con rol `user-api` (esas cuentas son solo para la integración AMYR). Los admins tienen además un acceso directo "Panel Vendedor" hacia `/branch-manager` desde el menú de usuario.

### 3.2 Modelo de dominio

- **Warehouse** (constantes `Warehouse::BUSINESS_WAREHOUSE` = `ALMACEN` / `BUSINESS_BRANCH` = `SUCURSAL`) vs **Branch**: un Branch es un punto de venta (tiene usuarios, precios, cierres de caja); el stock se rastrea por almacén (`WarehouseStock`) y por producto-por-sucursal (`ProductStock`), con `WarehouseStockHistory` como bitácora auditada de movimientos (ver `WarehouseStockHistoryObserver`).
- **Sale** → `SaleItem` / `SaleItemService` (una línea puede ser producto o servicio) → `SalePayment` (soporta pagos parciales/crédito, reconciliados vía `CreditService`) → `CashBoxClosing`/`CashMovement` para el cuadre de caja.
- Precios de **Product**: `Price` (polimórfico `priceable`) más los traits `HasPricesAndPromotions` / `HasPricesByBranch` habilitan precios por sucursal y por promoción; `OpticalProperty` guarda atributos de lente (rangos de esfera/cilindro configurados en `config/cerisier.php`).
- **Integración SIAT**: `SiatProperty`, `SiatCufd`, las tablas `SiatData*` y el namespace de modelos `Siat/` respaldan la facturación electrónica de Bolivia. `SiatService`/`SiatCodigos`/`SiatOperaciones` envuelven el paquete `amyrit/siat-bolivia-client`; los DAOs en `app/DAOs` + `app/Interfaces` sincronizan catálogos SIAT (tipos de documento, tipos de producto/servicio, unidades de medida).
- **Integración AMYR**: `config/amyr.php` configura una URL base REST para un sistema externo "AMYR"; `AmyrCatalogsService`, `AmyrCustomerApiService`, `AmyrEventsApiService`, `AmyrUserApiService` y el modelo `AmyrConnectionBranch` se comunican con él.
- `config/cerisier.php` guarda constantes propias del negocio (nombre de empresa, moneda `BOB`, paso/rango de lentes, paginación, tipos de cliente).

---

## 4. Modelos Eloquent

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Product extends Model
{
    use HasFactory, SoftDeletes;

    // Usar $fillable explícito, nunca $guarded = []
    protected $fillable = ['name', 'sku', 'price', 'stock', 'status'];

    protected $casts = [
        'price'  => 'decimal:2',
        'status' => ProductStatus::class, // Backed Enum
    ];

    // Relaciones — nunca default eager loading en el modelo
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes con nombre descriptivo en inglés
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Active);
    }
}
```

---

## 5. Componentes Livewire (con Volt o clase)

- Ubicación: `app/Livewire/` (clases) o `resources/views/livewire/` (Volt single-file).
- Nunca poner lógica de DB directamente en el componente — llamar al Service.
- Usar `#[Validate]` para validación inline en propiedades.
- Usar `#[On]` para eventos entre componentes.

```php
<?php

namespace App\Livewire;

use App\Services\ProductService;
use Livewire\Component;
use Livewire\Attributes\Validate;

final class CreateProductForm extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|numeric|min:0')]
    public float $price = 0.0;

    // El Service se inyecta en el método de acción
    public function save(ProductService $service): void
    {
        $this->validate();

        // Delegar al service — nunca DB:: aquí
        $service->create($this->only(['name', 'price']));

        $this->reset();
        $this->dispatch('product-created');
    }

    public function render()
    {
        return view('livewire.create-product-form');
    }
}
```

---

## 6. Filament Resources (v4)

- Los Resources **solo** definen esquemas de formulario, columnas de tabla y acciones de UI.
- Las acciones que modifican datos llaman al Service correspondiente.
- Usar `Filament\Actions\Action::make()` con `->action(fn() => $service->método())`.

```php
// Dentro de un Resource, ejemplo de acción que usa Service
Action::make('approve')
    ->label('Aprobar')
    ->color('success')
    ->action(function (Order $record, OrderService $service): void {
        // Delegar al service — nunca lógica de negocio aquí
        $service->approve($record);
    });
```

---

## 7. CSS — Tailwind 4 + DaisyUI 5

- Usar **clases de DaisyUI** para componentes UI: `btn`, `card`, `modal`, `badge`, `alert`, `table`, `input`, `select`, `drawer`, etc.
- Usar **Tailwind utilities** solo para spacing, sizing y overrides que DaisyUI no cubre.
- El tema se configura vía variables CSS de DaisyUI en `resources/css/app.css`.
- **No usar** clases de Bootstrap ni estilos inline.
- Las vistas Blade que usan Livewire deben tener un único elemento raíz.

```html
<!-- Correcto: DaisyUI + Tailwind -->
<div class="card bg-base-100 shadow-xl w-full max-w-md">
    <div class="card-body">
        <h2 class="card-title">{{ $title }}</h2>
        <p class="text-base-content/70">{{ $description }}</p>
        <div class="card-actions justify-end">
            <button class="btn btn-primary">Guardar</button>
        </div>
    </div>
</div>
```

---

## 8. Comandos

No hay `docker-compose.yml` en este repositorio (el `.devcontainer` referencia uno que no existe aquí). En este entorno PHP, Composer y npm corren directo — no anteponer `docker compose exec app`.

```bash
# Instalar
composer install
npm install

# Levantar la app (server + queue + logs + vite, concurrente)
composer run dev

# O por separado
php artisan serve
npm run dev            # vite dev server
npm run build           # build de producción

# Base de datos
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed --class=RoleSeeder

# Tests (Pest). Feature tests usan RefreshDatabase + sqlite :memory: (ver phpunit.xml)
php artisan test
vendor/bin/pest
vendor/bin/pest tests/Feature/Auth/AuthenticationTest.php   # un solo archivo
vendor/bin/pest --filter="can register"                     # un solo test por nombre

# Generar clases
php artisan make:model Product -mfs
php artisan make:livewire Branch/InventoryBranch
php artisan make:filament-resource Product --generate
# No existe generador artisan para Services — crear app/Services/*.php a mano
```

---

## 9. Convenciones de nomenclatura

| Artefacto | Formato | Ejemplo |
|-----------|---------|---------|
| Modelos | PascalCase singular | `ProductVariant` |
| Tablas DB | snake_case plural | `product_variants` |
| Services | PascalCase + sufijo `Service` | `InventoryService` |
| Livewire | PascalCase descriptivo | `ProductStockForm` |
| Filament Resource | PascalCase + sufijo `Resource` | `ProductResource` |
| Enums | PascalCase, valores PascalCase | `OrderStatus::Pending` |
| Migrations | timestamp + snake_case | `2025_07_01_create_orders_table` |
| Rutas | kebab-case | `/product-variants/{id}` |
| Variables JS/Blade | camelCase | `$productList`, `totalAmount` |

---

## 10. Tests (Pest 3)

- Cada Service debe tener su test en `tests/Unit/Services/`.
- Cada ruta HTTP debe tener su test en `tests/Feature/`.
- Usar `RefreshDatabase` en Feature tests.
- Factories para todos los modelos nuevos.

```php
// Ejemplo de test de Service
it('crea una orden y descuenta stock correctamente', function (): void {
    $product = Product::factory()->withStock(10)->create();

    $order = app(OrderService::class)->createOrder([
        'items' => [['product_id' => $product->id, 'quantity' => 3]],
    ], userId: 1);

    expect($order)->toBeInstanceOf(Order::class)
        ->and($product->fresh()->stock)->toBe(7);
});
```

---

## 11. Checklist antes de terminar cualquier tarea

- [ ] Código en inglés, comentarios en español
- [ ] Lógica de DB en un Service con `DB::transaction()` cuando corresponde
- [ ] Modelos con `$fillable` explícito y `$casts`
- [ ] Sin N+1: eager loading en el punto de consulta (`::with([...])`)
- [ ] Validación en Form Request o atributo `#[Validate]`
- [ ] Test escrito y pasando (`php artisan test`)
- [ ] Sin `dd()`, `var_dump()` ni `dump()` en el código final
- [ ] Migraciones con `down()` implementado