<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'image_path',
        'description',
        'is_active',
        'supplier_id'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function opticalProperties(): HasOne
    {
        return $this->hasOne(OpticalProperty::class);
    }
}
