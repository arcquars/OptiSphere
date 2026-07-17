<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAuthentication;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ProductAuthenticationService
{
    /**
     * Opciones para el buscador: únicamente los productos que el cliente compró,
     * etiquetados con su código, nombre y la cantidad total acumulada comprada.
     *
     * @return array<int, string> [product_id => "Código: cod - Nombre (Comprados: N)"]
     */
    public function purchasedProductOptions(int $customerId): array
    {
        // Cantidad comprada por producto (excluyendo ventas anuladas)
        $quantities = $this->purchasedQuantitiesByProduct($customerId);

        if ($quantities === []) {
            return [];
        }

        // Se incluyen productos soft-deleted que el cliente igualmente compró
        $products = Product::withTrashed()
            ->whereIn('id', array_keys($quantities))
            ->get(['id', 'code', 'name']);

        $options = [];
        foreach ($products as $product) {
            $total = $quantities[$product->id] ?? 0;
            $options[$product->id] = "Código: {$product->code} - {$product->name} (Comprados: {$this->formatQuantity($total)})";
        }

        return $options;
    }

    /**
     * Cantidad total de unidades que el cliente compró de un producto,
     * excluyendo ventas anuladas.
     */
    public function purchasedQuantity(int $customerId, int $productId): float
    {
        return (float) SaleItem::query()
            ->where('salable_type', Product::class)
            ->where('salable_id', $productId)
            ->whereHas('sale', fn ($query) => $query
                ->where('customer_id', $customerId)
                ->where('status', '!=', Sale::SALE_STATUS_VOIDED))
            ->sum('quantity');
    }

    /**
     * Registra la autenticación de un producto validando que no se supere
     * la cantidad total de unidades compradas por el cliente.
     *
     * @throws ValidationException si se excede el tope de unidades compradas
     * @throws \Throwable si la transacción falla
     */
    public function authenticate(int $customerId, array $data): ProductAuthentication
    {
        $productId = (int) $data['product_id'];

        return DB::transaction(function () use ($customerId, $productId, $data): ProductAuthentication {
            $purchased = $this->purchasedQuantity($customerId, $productId);

            // Bloquea las filas existentes para evitar condiciones de carrera en el conteo
            $existing = ProductAuthentication::query()
                ->where('product_id', $productId)
                ->where('frequent_customer_id', $customerId)
                ->lockForUpdate()
                ->count();

            if ($existing + 1 > $purchased) {
                throw ValidationException::withMessages([
                    'product_id' => "Ya autenticó {$existing} de {$this->formatQuantity($purchased)} unidades compradas de este producto. No puede registrar más.",
                ]);
            }

            return ProductAuthentication::create([
                'product_id' => $productId,
                'cliente' => $data['cliente'],
                'fecha_compra' => $data['fecha_compra'],
                'frequent_customer_id' => $customerId,
            ]);
        });
    }

    /**
     * Aprueba o desaprueba una autenticación desde el panel de administración,
     * dejando traza de quién la aprobó y cuándo.
     */
    public function setApproval(ProductAuthentication $authentication, bool $approved): ProductAuthentication
    {
        $authentication->update([
            'is_authentication' => $approved,
            // Al desaprobar se limpia la traza de auditoría previa
            'authentication_approved_date' => $approved ? now() : null,
            'authentication_approved_by' => $approved ? Auth::user()?->name : null,
        ]);

        return $authentication;
    }

    /**
     * Genera la URL pública del certificado de autenticidad.
     *
     * El ID se encripta y se hacen URL-safe los caracteres conflictivos del base64
     * (+ y /), tal como los revierte ProductAuthenticationController::show().
     */
    public function buildPublicUrl(ProductAuthentication $authentication): string
    {
        $encrypted = Crypt::encrypt($authentication->id);
        $urlSafeToken = str_replace(['+', '/'], ['-', '_'], $encrypted);

        return route('product.authentication', ['token' => $urlSafeToken]);
    }

    /**
     * Suma de unidades compradas agrupada por producto (excluyendo anuladas).
     *
     * @return array<int, float> [salable_id => total]
     */
    private function purchasedQuantitiesByProduct(int $customerId): array
    {
        return SaleItem::query()
            ->where('salable_type', Product::class)
            ->whereHas('sale', fn ($query) => $query
                ->where('customer_id', $customerId)
                ->where('status', '!=', Sale::SALE_STATUS_VOIDED))
            ->selectRaw('salable_id, SUM(quantity) as total')
            ->groupBy('salable_id')
            ->pluck('total', 'salable_id')
            ->map(fn ($total): float => (float) $total)
            ->all();
    }

    /**
     * Formatea una cantidad mostrando decimales solo cuando existen.
     */
    private function formatQuantity(float $quantity): string
    {
        return $quantity == (int) $quantity
            ? (string) (int) $quantity
            : rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.');
    }
}
