<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Atributo para obtener el nombre de la ubicación de origen.
     * Resuelve dinámicamente si es una Sucursal o un Almacén.
     */
    public function fromLocationName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->from_location_type === self::LOCATION_TYPE_BRANCH) {
                    return \App\Models\Branch::find($this->from_location_id)?->name ?? 'Sucursal no encontrada';
                }
                
                if ($this->from_location_type === self::LOCATION_TYPE_warehouse) {
                    // Asumiendo que el modelo se llama Warehouse
                    return \App\Models\Warehouse::find($this->from_location_id)?->name ?? 'Almacén no encontrado';
                }

                return 'N/A';
            }
        );
    }

    /**
     * Atributo para obtener el nombre de la ubicación de destino.
     */
    public function toLocationName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->to_location_type === self::LOCATION_TYPE_BRANCH) {
                    return \App\Models\Branch::find($this->to_location_id)?->name ?? 'Sucursal no encontrada';
                }
                
                if ($this->to_location_type === self::LOCATION_TYPE_warehouse) {
                    return \App\Models\Warehouse::find($this->to_location_id)?->name ?? 'Almacén no encontrado';
                }

                return 'N/A';
            }
        );
    }
}
