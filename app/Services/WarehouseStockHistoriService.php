<?php
namespace App\Services;

use App\Models\WarehouseIncome;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Illuminate\Support\Facades\DB;

class WarehouseStockHistoriService
{
    public function updateSingleIncome($warehouseStockHistoryId, $amount){
        $wareHouseStockHistory = WarehouseStockHistory::find($warehouseStockHistoryId);

        DB::transaction(function () use ($wareHouseStockHistory, $amount) {
            $wareHouseStockHistory->new_quantity = $wareHouseStockHistory->old_quantity + $amount;
            $wareHouseStockHistory->difference = $amount;
            $wareHouseStockHistory->save();

            $warehouseStock = WarehouseStock::find($wareHouseStockHistory->warehouse_stock_id);
            $warehouseStock->quantity = $amount;
            $warehouseStock->save();

        });
    }
}