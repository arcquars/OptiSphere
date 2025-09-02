<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    const TYPE_NORMAL = "normal";
    const TYPE_ESPECIAL = "especial";
    const TYPE_MAYORISTA = "mayorista";

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'price',
        'product_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
