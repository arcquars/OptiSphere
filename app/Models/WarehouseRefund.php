<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseRefund extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = "ACTIVE";
    public const STATUS_VOID = "VOID";

    /**
     * @var list<string>
     */
    protected $fillable = [
        'warehouse_id',
        'branch_id',
        'user_id',
        'base_code',
        'status',
        'refund_date'
    ];

    public function warehouse(){
        return $this->belongsTo(Warehouse::class);
    }

    public function branch(){
        return $this->belongsTo(Branch::class);
    }

    public function warehouseStockHistory(){
        // aumentar mevement_type para diferenciar entre ingresos, entregas y devoluciones
        return $this->hasMany(WarehouseStockHistory::class, 'type_id')->where('movement_type', WarehouseStockHistory::MOVEMENT_TYPE_REFUND);
    }
}
