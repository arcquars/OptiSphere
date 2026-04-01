<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseRefund extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'warehouse_id',
        'branch_id',
        'user_id',
        'base_code',
        'refund_date'
    ];

    public function warehouse(){
        return $this->belongsTo(Warehouse::class);
    }

    public function branch(){
        return $this->belongsTo(Branch::class);
    }
}
