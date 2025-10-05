<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'user_id',
        'total_amount',
        'final_discount',
        'final_total',
        'status',
        'payment_method',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'final_discount' => 'float',
        'final_total' => 'float',
    ];

    /**
     * Relación con el detalle de la venta.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Relación con la sucursal.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con el cliente.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación con el usuario que realizó la venta.
     */
    public function user(): BelongsTo
    {
        // Asumiendo que tienes un modelo User
        return $this->belongsTo(User::class);
    }

}
