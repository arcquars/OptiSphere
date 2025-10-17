<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItemService extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sale_item_id',
        'service_id',
        'quantity',
        'price_per_unit',
        'promotion_id',
        'promotion_discount_rate',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'float',
        'price_per_unit' => 'float',
        'promotion_discount_rate' => 'float',
        'subtotal' => 'float',
    ];

    /**
     * El item de venta (usualmente un Producto) al que se adjunta este servicio.
     */
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    /**
     * El servicio específico que fue vendido.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public static function calculateSubtotal($quantity, $price_per_unit, $promotion_discount_rate){
        $subtotal = $quantity * $price_per_unit;
        if($promotion_discount_rate != null){
            if($promotion_discount_rate != 0)
                $subtotal = $quantity * ($price_per_unit - ($price_per_unit*($promotion_discount_rate/100)));
        }
        return number_format($subtotal, 2);
    }
}
