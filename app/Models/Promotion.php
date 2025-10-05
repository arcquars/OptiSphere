<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'discount_percentage',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'discount_percentage' => 'float',
    ];

    /**
     * Obtiene todos los productos a los que aplica esta promoción.
     */
    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'promotionable');
    }

    /**
     * Obtiene todos los servicios a los que aplica esta promoción.
     */
    public function services(): MorphToMany
    {
        return $this->morphedByMany(Service::class, 'promotionable');
    }

}
