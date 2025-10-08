<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    const LOCATION_TYPE_BRANCH = "SUCURSAL";
    const LOCATION_TYPE_warehouse = "ALMACEN";

    // Tipos de Movimiento
    const TYPE_IN = "ENTRADA";
    const TYPE_OUT = "SALIDA";
    const TYPE_TRANSFER = "TRANSFERENCIA";
    const TYPE_SALE = "VENTA"; // Usaremos este
    const TYPE_ADJUSTMENT = "AJUSTE";

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

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
