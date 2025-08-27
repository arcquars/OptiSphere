<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpticalProperty extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'sphere',
        'cylinder',
        'axis',
        'add'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
