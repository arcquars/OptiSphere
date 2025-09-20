<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseStockHistory extends Model
{
    const MOVEMENT_TYPE_INCOME = "INGRESO";
    const MOVEMENT_TYPE_REFUND = "DEVOLUCION";
    const MOVEMENT_TYPE_DELIVERY = "ENTREGA_SUCURSAL";
    /**
     * @var list<string>
     */
    protected $fillable = [
        'warehouse_stock_id',
        'old_quantity',
        'new_quantity',
        'difference',
        'movement_type',
        'type_id'
    ];
}
