<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'salable_id',
        'salable_type',
        'quantity',
        'base_price',
        'promotion_id',
        'promotion_discount_rate',
        'final_price_per_unit',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'float',
        'base_price' => 'float',
        'promotion_discount_rate' => 'float',
        'final_price_per_unit' => 'float',
        'subtotal' => 'float',
    ];

    /**
     * Relación con el encabezado de la venta.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación polimórfica con el elemento vendido (Product o Service).
     */
    public function salable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relación con la promoción aplicada (opcional).
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}
