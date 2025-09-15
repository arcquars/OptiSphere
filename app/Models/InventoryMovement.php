<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    const LOCATION_TYPE_BRANCH = "SUCURSAL";
    const LOCATION_TYPE_warehouse = "ALMACEN";

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'from_location_type',
        'from_location_id',
        'to_location_type',
        'to_location_id',
        'old_quantity',
        'new_quantity',
        'difference',
        'type',
        'description',
        'user_id',
    ];
}
