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
        'refund_date'
    ];
}
