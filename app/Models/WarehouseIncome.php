<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseIncome extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = "ACTIVE";
    public const STATUS_VOID = "VOID";
    
    /**
     * @var list<string>
     */
    protected $fillable = [
        'warehouse_id',
        'user_id',
        'base_code',
        'status',
        'income_date'
    ];

    public function warehouse(){
        return $this->belongsTo(Warehouse::class);
    }
}
