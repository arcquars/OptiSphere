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
        'start_date' => 'date',
        'end_date' => 'date',
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

    /**
     * Relación polimórfica genérica para obtener todos los ítems
     * (productos y/o servicios) a los que aplica esta promoción.
     * La clave 'promotionable' corresponde a las columnas promotionable_id y promotionable_type
     * en la tabla pivote 'promotionables'.
     *
     * @return MorphToMany
     */
    public function promotionables(): MorphToMany
    {
        // En este caso, solo mapeamos a Product, pero Laravel maneja automáticamente
        // los diferentes tipos (Service, Product) gracias al Trait que definimos.
        return $this->morphToMany(Product::class, 'promotionable');
    }


}
