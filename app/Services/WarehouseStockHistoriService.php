<?php
namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ProductStock;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseIncome;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseStockHistoriService
{
    public function createSingleIncome($warehouseIncomeId, $productId, $amount){
        try{
            DB::transaction(function () use ($warehouseIncomeId, $productId, $amount) {
                $wareHouseIncome = WarehouseIncome::find($warehouseIncomeId);
                $attributes = [
                    'product_id' => $productId,
                    'warehouse_id' => $wareHouseIncome->warehouse_id, // Ejemplo: asume que es el almacén 1
                ];
                // Buscar el registro de stock por su ID.
                $warehouseStock = WarehouseStock::firstOrCreate($attributes, [
                    'quantity' => 0 // Inicializa la cantidad en 0 si es un nuevo registro
                ]);

                $oldQuantity = $warehouseStock->quantity;
                $newQuantity = $oldQuantity + $amount;

                $warehouseStock->increment('quantity', $amount);

                if ($amount != 0) {
                    WarehouseStockHistory::create([
                        'warehouse_stock_id' => $warehouseStock->id,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'difference' => $amount,
                        'movement_type' => WarehouseStockHistory::MOVEMENT_TYPE_INCOME,
                        'type_id' => $wareHouseIncome->id
                    ]);
                }
            });
        } catch (Exception $e) {
            dd($e);
        }

    }

    public function updateSingleIncome($warehouseStockHistoryId, $amount){
        $wareHouseStockHistory = WarehouseStockHistory::find($warehouseStockHistoryId);

        $difference = $wareHouseStockHistory->difference;
        DB::transaction(function () use ($difference, $wareHouseStockHistory, $amount) {
            $wareHouseStockHistory->new_quantity = $wareHouseStockHistory->old_quantity + $amount;
            $wareHouseStockHistory->difference = $amount;
            $wareHouseStockHistory->save();

            $warehouseStock = WarehouseStock::find($wareHouseStockHistory->warehouse_stock_id);
            $warehouseStock->quantity = $warehouseStock->quantity +  $amount - $difference;
            $warehouseStock->save();

        });
    }

    /**
     * Agrega un producto NUEVO a una entrega ya existente.
     * Sale del almacén y entra a la sucursal destino de esa entrega.
     */
    public function createSingleDelivery($warehouseDeliveryId, $productId, $amount)
    {
        DB::transaction(function () use ($warehouseDeliveryId, $productId, $amount) {
            $warehouseDelivery = WarehouseDelivery::find($warehouseDeliveryId);

            $warehouseStock = WarehouseStock::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseDelivery->warehouse_id],
                ['quantity' => 0]
            );

            $oldWarehouseQty = $warehouseStock->quantity;
            $newWarehouseQty = $oldWarehouseQty - $amount;

            if ($newWarehouseQty < 0) {
                throw new Exception('No hay suficiente stock en el almacén para esta entrega.');
            }

            $warehouseStock->quantity = $newWarehouseQty;
            $warehouseStock->save();

            if ($amount != 0) {
                WarehouseStockHistory::create([
                    'warehouse_stock_id' => $warehouseStock->id,
                    'old_quantity' => $oldWarehouseQty,
                    'new_quantity' => $newWarehouseQty,
                    'difference' => $amount,
                    'movement_type' => WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY,
                    'type_id' => $warehouseDelivery->id,
                ]);

                $productStock = ProductStock::firstOrCreate(
                    ['product_id' => $productId, 'branch_id' => $warehouseDelivery->branch_id],
                    ['quantity' => 0]
                );

                $oldBranchQty = $productStock->quantity;
                $newBranchQty = $oldBranchQty + $amount;
                $productStock->quantity = $newBranchQty;
                $productStock->save();

                InventoryMovement::create([
                    'product_id' => $productId,
                    'from_location_type' => InventoryMovement::LOCATION_TYPE_warehouse,
                    'from_location_id' => $warehouseDelivery->warehouse_id,
                    'to_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                    'to_location_id' => $warehouseDelivery->branch_id,
                    'old_quantity' => $oldBranchQty,
                    'new_quantity' => $newBranchQty,
                    'difference' => $amount,
                    'type' => WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY,
                    'type_id' => $warehouseDelivery->id,
                    'user_id' => Auth::id(),
                ]);
            }
        });
    }

    /**
     * Ajusta la cantidad de un producto que YA estaba en la entrega.
     * Recalcula almacén y sucursal según el delta entre el monto anterior y el nuevo.
     */
    public function updateSingleDelivery($warehouseStockHistoryId, $newAmount)
    {
        DB::transaction(function () use ($warehouseStockHistoryId, $newAmount) {
            $warehouseStockHistory = WarehouseStockHistory::find($warehouseStockHistoryId);
            $warehouseDelivery = $warehouseStockHistory->warehouseDelivery; // belongsTo type_id
            $warehouseStock = $warehouseStockHistory->warehouseStock;
            $productId = $warehouseStock->product_id;

            $oldAmount = $warehouseStockHistory->difference;
            $delta = $newAmount - $oldAmount; // >0: se entrega más | <0: se entrega menos

            // --- Almacén ---
            $newWarehouseQty = $warehouseStock->quantity - $delta;
            if ($newWarehouseQty < 0) {
                throw new Exception('No hay suficiente stock en el almacén para este ajuste.');
            }

            // --- Sucursal ---
            $productStock = ProductStock::firstOrCreate(
                ['product_id' => $productId, 'branch_id' => $warehouseDelivery->branch_id],
                ['quantity' => 0]
            );
            $oldBranchQty = $productStock->quantity;
            $newBranchQty = $oldBranchQty + $delta;

            if ($newBranchQty < 0) {
                throw new Exception('El stock en la sucursal es menor a lo que se quiere ajustar (puede que ya se haya vendido).');
            }

            // Persistir warehouse_stock_histories (old_quantity histórico se mantiene fijo)
            $warehouseStockHistory->new_quantity = $warehouseStockHistory->old_quantity - $newAmount;
            $warehouseStockHistory->difference = $newAmount;
            $warehouseStockHistory->save();

            // Persistir almacén
            $warehouseStock->quantity = $newWarehouseQty;
            $warehouseStock->save();

            // Persistir sucursal
            $productStock->quantity = $newBranchQty;
            $productStock->save();

            // Registrar el ajuste en inventory_movements (trazabilidad)
            if ($delta != 0) {
                InventoryMovement::create([
                    'product_id' => $productId,
                    'from_location_type' => InventoryMovement::LOCATION_TYPE_warehouse,
                    'from_location_id' => $warehouseDelivery->warehouse_id,
                    'to_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                    'to_location_id' => $warehouseDelivery->branch_id,
                    'old_quantity' => $oldBranchQty,
                    'new_quantity' => $newBranchQty,
                    'difference' => $delta,
                    'type' => WarehouseStockHistory::MOVEMENT_TYPE_ADJUST_DELIVERY,
                    'type_id' => $warehouseDelivery->id,
                    'user_id' => Auth::id(),
                ]);
            }
        });
    }
}