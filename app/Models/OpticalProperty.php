<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpticalProperty extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'base_code',
        'type',
        'sphere',
        'cylinder',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockByWarehouse($warehouseId){
        $warehouseStock = WarehouseStock::where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)->first();
        $quantity = 0;
        if($warehouseStock)
            $quantity = $warehouseStock->quantity;
        return $quantity;
    }
}
