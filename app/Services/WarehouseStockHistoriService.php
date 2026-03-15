<?php
namespace App\Services;

use App\Models\WarehouseIncome;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Exception;
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
}