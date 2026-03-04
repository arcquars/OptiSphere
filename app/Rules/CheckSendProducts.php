<?php

namespace App\Rules;

use App\Models\WarehouseStockHistory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class CheckSendProducts implements ValidationRule
{
    protected $warehouseId;
    protected $type;

    public function __construct($warehouseId, $type)
    {
        $this->warehouseId = $warehouseId;
        $this->type = $type;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $warehouseStockHistories = WarehouseStockHistory::where('movement_type', $this->type)
            ->where('type_id', $this->warehouseId)->get();
        
        foreach($warehouseStockHistories as $warehouseStockHistory){
            if($warehouseStockHistory->difference > $warehouseStockHistory->warehouseStock->quantity){
                $fail('El stock actual es menor al registro de este INGRESO');
            }
        }
        
    }
}
