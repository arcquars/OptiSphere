<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    protected $appends = ['is_service', 'type_label'];

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

    protected function isService(): Attribute
    {
        return Attribute::get(fn () => $this->salable instanceof Service);
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->is_service ? 'Servicio' : 'Producto');
    }

    /**
     * Relación con la promoción aplicada (opcional).
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}
