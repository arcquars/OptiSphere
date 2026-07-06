<?php
namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\OpticalProperty;
use App\Models\ProductStock;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseIncome;
use App\Models\WarehouseRefund;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Carbon\Carbon;
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

    /**
     * Traslada TODOS los productos de una ENTREGA desde su sucursal origen
     * hacia una sucursal destino, pasando por el almacén de origen.
     *
     * El traslado se modela en dos tramos para mantener consistente el ledger:
     *   Tramo 1 (DEVOLUCION): sucursal origen -> almacén.
     *   Tramo 2 (ENTREGA):    almacén -> sucursal destino.
     * El stock neto del almacén no cambia (entra y sale la misma cantidad).
     *
     * @param  int $originDeliveryId   ID del registro de ENTREGA (WarehouseDelivery) de origen.
     * @param  int $destinationBranchId ID de la sucursal destino.
     * @param  string|null $baseCode    Si se indica, solo se trasladan los productos de ese código óptico.
     * @return WarehouseDelivery La nueva entrega generada hacia la sucursal destino.
     * @throws Exception Si el stock de la sucursal origen es insuficiente o el destino es inválido.
     */
    public function transferBranchToBranch(int $originDeliveryId, int $destinationBranchId, ?string $baseCode = null): WarehouseDelivery
    {
        return DB::transaction(function () use ($originDeliveryId, $destinationBranchId, $baseCode) {
            $originDelivery = WarehouseDelivery::findOrFail($originDeliveryId);
            $originBranchId = $originDelivery->branch_id;
            $warehouseId = $originDelivery->warehouse_id;

            if ((int) $originBranchId === (int) $destinationBranchId) {
                throw new Exception('La sucursal destino debe ser diferente a la sucursal origen.');
            }

            // Código óptico a registrar en los movimientos maestros
            $masterBaseCode = $baseCode ?? $originDelivery->base_code;

            // Líneas realmente entregadas en esta ENTREGA (una fila por producto)
            $deliveryLines = WarehouseStockHistory::where('movement_type', WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY)
                ->where('type_id', $originDelivery->id)
                ->with('warehouseStock')
                ->get();

            // Registro maestro del tramo 1: DEVOLUCION (sucursal origen -> almacén)
            $warehouseRefund = WarehouseRefund::create([
                'warehouse_id' => $warehouseId,
                'branch_id'    => $originBranchId,
                'user_id'      => Auth::id(),
                'base_code'    => $masterBaseCode,
                'status'       => WarehouseRefund::STATUS_ACTIVE,
                'refund_date'  => Carbon::now(),
            ]);

            // Registro maestro del tramo 2: ENTREGA (almacén -> sucursal destino)
            $warehouseDelivery = WarehouseDelivery::create([
                'warehouse_id'  => $warehouseId,
                'branch_id'     => $destinationBranchId,
                'user_id'       => Auth::id(),
                'base_code'     => $masterBaseCode,
                'status'        => WarehouseDelivery::STATUS_ACTIVE,
                'delivery_date' => Carbon::now(),
            ]);

            foreach ($deliveryLines as $line) {
                $productId = $line->warehouseStock->product_id;

                // Si se especificó un base_code, solo trasladar los productos de ese código óptico
                if ($baseCode !== null) {
                    $op = OpticalProperty::where('product_id', $productId)->first();
                    if (! $op || $op->base_code != $baseCode) {
                        continue;
                    }
                }

                $amount = (int) $line->difference;

                if ($amount <= 0) {
                    continue;
                }

                // ===== Tramo 1: DEVOLUCION (sucursal origen -> almacén) =====

                // Descontar del stock de la sucursal origen
                $originStock = ProductStock::firstOrCreate(
                    ['product_id' => $productId, 'branch_id' => $originBranchId],
                    ['quantity' => 0]
                );

                if ($originStock->quantity < $amount) {
                    throw new Exception("Stock insuficiente en la sucursal origen para el producto #{$productId} (disponible: {$originStock->quantity}, requerido: {$amount}).");
                }

                $oldOriginQty = $originStock->quantity;
                $newOriginQty = $oldOriginQty - $amount;
                $originStock->quantity = $newOriginQty;
                $originStock->save();

                // Ingresar al stock del almacén
                $warehouseStock = WarehouseStock::firstOrCreate(
                    ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                    ['quantity' => 0]
                );

                $oldWarehouseInQty = $warehouseStock->quantity;
                $newWarehouseInQty = $oldWarehouseInQty + $amount;
                $warehouseStock->quantity = $newWarehouseInQty;
                $warehouseStock->save();

                WarehouseStockHistory::create([
                    'warehouse_stock_id' => $warehouseStock->id,
                    'old_quantity'       => $oldWarehouseInQty,
                    'new_quantity'       => $newWarehouseInQty,
                    'difference'         => $amount,
                    'movement_type'      => WarehouseStockHistory::MOVEMENT_TYPE_REFUND,
                    'type_id'            => $warehouseRefund->id,
                ]);

                InventoryMovement::create([
                    'product_id'         => $productId,
                    'from_location_type' => InventoryMovement::LOCATION_TYPE_BRANCH,
                    'from_location_id'   => $originBranchId,
                    'to_location_type'   => InventoryMovement::LOCATION_TYPE_warehouse,
                    'to_location_id'     => $warehouseId,
                    'old_quantity'       => $oldOriginQty,
                    'new_quantity'       => $newOriginQty,
                    'difference'         => $amount,
                    'type'               => WarehouseStockHistory::MOVEMENT_TYPE_REFUND,
                    'type_id'            => $warehouseRefund->id,
                    'user_id'            => Auth::id(),
                ]);

                // ===== Tramo 2: ENTREGA (almacén -> sucursal destino) =====

                // Descontar del stock del almacén (neto 0 respecto al tramo 1)
                $oldWarehouseOutQty = $warehouseStock->quantity;
                $newWarehouseOutQty = $oldWarehouseOutQty - $amount;
                $warehouseStock->quantity = $newWarehouseOutQty;
                $warehouseStock->save();

                WarehouseStockHistory::create([
                    'warehouse_stock_id' => $warehouseStock->id,
                    'old_quantity'       => $oldWarehouseOutQty,
                    'new_quantity'       => $newWarehouseOutQty,
                    'difference'         => $amount,
                    'movement_type'      => WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY,
                    'type_id'            => $warehouseDelivery->id,
                ]);

                // Ingresar al stock de la sucursal destino
                $destinationStock = ProductStock::firstOrCreate(
                    ['product_id' => $productId, 'branch_id' => $destinationBranchId],
                    ['quantity' => 0]
                );

                $oldDestinationQty = $destinationStock->quantity;
                $newDestinationQty = $oldDestinationQty + $amount;
                $destinationStock->quantity = $newDestinationQty;
                $destinationStock->save();

                InventoryMovement::create([
                    'product_id'         => $productId,
                    'from_location_type' => InventoryMovement::LOCATION_TYPE_warehouse,
                    'from_location_id'   => $warehouseId,
                    'to_location_type'   => InventoryMovement::LOCATION_TYPE_BRANCH,
                    'to_location_id'     => $destinationBranchId,
                    'old_quantity'       => $oldDestinationQty,
                    'new_quantity'       => $newDestinationQty,
                    'difference'         => $amount,
                    'type'               => WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY,
                    'type_id'            => $warehouseDelivery->id,
                    'user_id'            => Auth::id(),
                ]);
            }

            return $warehouseDelivery;
        });
    }
}