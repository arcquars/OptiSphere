<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class WarehouseStockHistory extends Model
{
    const MOVEMENT_TYPE_INCOME = "INGRESO";
    const MOVEMENT_TYPE_REFUND = "DEVOLUCION";
    const MOVEMENT_TYPE_DELIVERY = "ENTREGA_SUCURSAL";
    const MOVEMENT_TYPE_VOID_DELIVERY = "ANULACION_ENTREGA_SUCURSAL";
    const MOVEMENT_TYPE_VOID_REFUND = "ANULACION_DEVOLUCION";
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

    public function warehouseStock(){
        return $this->belongsTo(WarehouseStock::class);
    }

    public function warehouseIncome() {
        return $this->belongsTo(WarehouseIncome::class, 'type_id');
    }

    public function warehouseRefund() {
        return $this->belongsTo(WarehouseRefund::class, 'type_id');
    }

    public function warehouseDelivery() {
        return $this->belongsTo(WarehouseDelivery::class, 'type_id');
    }

    public function warehouse_m(){
        switch($this->movement_type){
            case "INGRESO":
                return $this->belongsTo(WarehouseIncome::class, 'type_id');
            case "DEVOLUCION":
                return $this->belongsTo(WarehouseRefund::class, 'type_id');
            case "ENTREGA_SUCURSAL":
                return $this->belongsTo(WarehouseDelivery::class, 'type_id');
            case "ANULACION_ENTREGA_SUCURSAL":
                return $this->belongsTo(WarehouseDelivery::class, 'type_id');

        }
    }

    public function dateMovementLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                $date = null;

                // Normalizamos a mayúsculas por si la BD tiene valores inconsistentes
                switch (strtoupper($this->movement_type)) {
                    case "INGRESO":
                        $date = $this->warehouseIncome()->first()?->created_at;
                        break;
                    case "DEVOLUCION":
                        $date = $this->warehouseRefund()->first()?->created_at;
                        break;
                    case "ENTREGA_SUCURSAL":
                        $date = $this->warehouseDelivery()->first()?->created_at;
                        break;
                    case "ANULACION_ENTREGA_SUCURSAL":
                        $date = $this->warehouseDelivery()->first()?->created_at;
                        break;
                    default:
                        $date = $this->created_at;
                        break;
                }

                // Si $date existe la formatea, si no, devuelve 'N/A' o la fecha actual
                return $date?->format('d/m/Y H:i') ?? 'N/A'; 
            },
        );
    }

    public function audits()
    {
        return $this->morphMany(\App\Models\Audit::class, 'auditable');
    }
}
