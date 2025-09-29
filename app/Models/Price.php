<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Price extends Model
{
    const TYPE_NORMAL = "normal";
    const TYPE_ESPECIAL = "especial";
    const TYPE_MAYORISTA = "mayorista";

    protected $fillable = [
        'type',
        'price',
        'branch_id',
        'priceable_id',
        'priceable_type',
    ];

    /**
     * Obtiene el modelo padre (Product o Service) al que pertenece el precio.
     */
    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


}
