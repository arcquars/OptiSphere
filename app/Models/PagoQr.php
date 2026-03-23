<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PagoQr extends Model
{
    const STATUS_PENDING = "PENDING";
    const STATUS_PAID = "PAID";
    const STATUS_CANCELLED = "CANCELLED";
    const STATUS_EXPIRED = "EXPIRED";
    protected $table = 'pago_qrs';

    protected $fillable = [
        'transaction_id',
        'qr_id',
        'amount',
        'currency',
        'description',
        'branch_code',
        'status',
        'payment_date',
        'payment_time',
        'qr_image',
        'extra_data',
        'sender_bank_code',
        'sender_name',
        'sender_document_id',
        'sender_account',
        'is_assigned',
        
    ];

    protected $casts = [
        'extra_data' => 'array',
        'payment_date' => 'datetime'
    ];

    protected function paymentTime(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? Carbon::createFromFormat('H:i:s', $value) : null,
            set: fn ($value) => $value instanceof Carbon ? $value->format('H:i:s') : $value,
        );
    }

    public function sales(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Sale::class, 
            'sale_partial_payment_qrs', 
            'pago_qr_id', 
            'sale_id'
        )
        ->withPivot('status')
        ->withPivot('amount')
        ->withTimestamps();
    }
    
}
