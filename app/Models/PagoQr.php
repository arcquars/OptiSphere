<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoQr extends Model
{
    protected $table = 'pagos_qrs';

    protected $fillable = [
        'transaction_id',
        'qr_id',
        'amount',
        'currency',
        'description',
        'branch_code',
        'status',
        'payment_date',
        'qr_image',
        'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'payment_date' => 'datetime',
    ];
}
