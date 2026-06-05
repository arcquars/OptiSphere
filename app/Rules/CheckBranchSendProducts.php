<?php

namespace App\Rules;

use App\Models\ProductStock;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseStockHistory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class CheckBranchSendProducts implements ValidationRule
{
    protected int $warehouseDeliveryId;
    public function __construct(int $warehouseDeliveryId)
    {
        $this->warehouseDeliveryId = $warehouseDeliveryId;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $warehouseDelivery = WarehouseDelivery::where('id', $this->warehouseDeliveryId)->first();

        $warehouseStockHistories = WarehouseStockHistory::where('movement_type', WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY)
            ->where('type_id', $this->warehouseDeliveryId)->get();
        // dd($warehouseStockHistories);
        foreach($warehouseStockHistories as $warehouseStockHistory){
            
            $branchStock = ProductStock::where('product_id', $warehouseStockHistory->warehouseStock->product_id)
                ->where('branch_id', $warehouseDelivery->branch_id)
                ->first();
            if($warehouseStockHistory->difference > $branchStock->quantity){
                $fail("El stock actual de la sucursal {$warehouseDelivery->branch->name} ({$branchStock->quantity}) es menor al registro de esta ENTREGA ({$warehouseStockHistory->difference})");
            }
        }
        
    }
}
