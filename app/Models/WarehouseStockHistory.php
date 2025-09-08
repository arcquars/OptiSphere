<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseStockHistory extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'warehouse_stock_id',
        'old_quantity',
        'new_quantity',
        'difference'
    ];
}
