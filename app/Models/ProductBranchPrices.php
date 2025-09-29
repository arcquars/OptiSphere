<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBranchPrices extends Model
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
        'user_id',
        'product_id',
        'branch_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
