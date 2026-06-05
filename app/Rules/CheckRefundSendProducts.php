<?php

namespace App\Rules;

use App\Models\ProductStock;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseRefund;
use App\Models\WarehouseStockHistory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class CheckRefundSendProducts implements ValidationRule
{
    protected int $warehouseRefundId;
    public function __construct(int $warehouseRefundId)
    {
        $this->warehouseRefundId = $warehouseRefundId;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $warehouseRefund = WarehouseRefund::where('id', $this->warehouseRefundId)->first();

        $warehouseStockHistories = WarehouseStockHistory::where('movement_type', WarehouseStockHistory::MOVEMENT_TYPE_REFUND)
            ->where('type_id', $this->warehouseRefundId)->get();
        foreach($warehouseStockHistories as $warehouseStockHistory){
            if($warehouseStockHistory->difference > $warehouseStockHistory->warehouseStock->quantity){
                $fail('El stock actual es menor al registro de esta DEVOLUCION');
            }
        }
        
    }
}
