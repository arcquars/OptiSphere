<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class InventoryService
{

    /**
     * Registra un movimiento de inventario y actualiza el stock de forma atómica.
     *
     * @param int $productId El ID del producto.
     * @param int $quantity La cantidad del movimiento (positivo para entrada, negativo para salida).
     * @param string $movementType El tipo de movimiento (ej: InventoryMovement::TYPE_IN).
     * @param int $userId ID del usuario que realiza el movimiento.
     * @param string $locationType Tipo de ubicación afectada (ej: InventoryMovement::LOCATION_TYPE_BRANCH).
     * @param int $locationId ID de la ubicación afectada (Sucursal o Almacén).
     * @param string $description Descripción del movimiento.
     * @param int|null $saleId ID de la venta asociada (OPCIONAL, solo si TYPE es VENTA).
     * @return ProductStock|bool
     */
    public function updateStock(
        int $productId,
        int $quantity,
        string $movementType,
        int $userId,
        string $locationType,
        int $locationId,
        string $description,
        ?int $saleId = null // <--- ¡NUEVO PARÁMETRO!
    ): ProductStock|bool
    {
        // Usamos solo ProductStock por simplicidad, ajusta si tienes WarehouseStock
        $stockModel = ProductStock::class;
        $locationKey = 'branch_id';

        Log::info('xxx pdm 1');
        try {
            DB::beginTransaction();
            Log::info('xxx pdm 2');
            // 1. OBTENER O CREAR EL STOCK (Bloqueo para concurrencia)
            $stock = $stockModel::lockForUpdate()->firstOrCreate( // <--- Se añade lockForUpdate() para seguridad
                ['product_id' => $productId, $locationKey => $locationId],
                ['quantity' => 0]
            );

            Log::info('xxx pdm 3');
            // 2. REGISTRAR EL MOVIMIENTO
            $oldQuantity = $stock->quantity;
            $newQuantity = $oldQuantity + $quantity;
            Log::info('xxx pdm 4');
            if ($newQuantity < 0) {
                // Manejar error si la cantidad resultante es negativa
                Log::info('xxx pdm 5');
                DB::rollBack();
                // Puedes lanzar una excepción específica para stock.
                throw new Exception("Stock insuficiente para el producto $productId. Disponible: $oldQuantity, Solicitado: " . abs($quantity));
            }
            Log::info('xxx pdm 6');
            InventoryMovement::create([
                'product_id' => $productId,
                'from_location_type' => $locationType,
                'from_location_id' => $locationId,
                'to_location_type' => $locationType,
                'to_location_id' => $locationId, // Para ventas el origen y destino del stock son iguales (la sucursal)
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'difference' => $quantity,
                'type' => $movementType,
                'description' => $description,
                'user_id' => $userId,
                'sale_id' => $saleId, // <--- ¡Guardamos el ID de la Venta!
            ]);

            Log::info('xxx pdm 7');
            // 3. ACTUALIZAR EL STOCK
            $stock->quantity = $newQuantity;
            $stock->save();
            Log::info('xxx pdm 8');
            DB::commit();

            return $stock;

        } catch (Exception $e) {
            Log::info('xxx pdm 9');
            DB::rollBack();
            // Retornamos falso y el servicio de venta manejará el error superior.
            // Es crucial no logear ni suprimir el error aquí, debe propagarse.
            return false;
        }
    }
}
