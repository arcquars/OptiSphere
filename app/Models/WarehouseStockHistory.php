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

        }
    }

    public function audits()
    {
        return $this->morphMany(\App\Models\Audit::class, 'auditable');
    }
}
