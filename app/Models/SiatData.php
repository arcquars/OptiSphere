<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiatData extends Model
{
    use HasFactory;

    protected $table = 'siat_datas';

    protected $fillable = [
        'tipo_catalogo',
        'codigo_clasificador',
        'descripcion',
        "siat_spv_id",
    ];


    /**
     * Define el tipo de catálogo que este modelo representa.
     * DEBE ser implementado por las clases hijas.
     * @var string|null
     */
    public static ?string $catalogoType = null;


    /**
     * Scope para filtrar automáticamente por el tipo de catálogo.
     * Esta función se llama automáticamente por Laravel al hacer consultas.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('catalogoType', function (Builder $builder) {
            if (static::$catalogoType) {
                $builder->where('tipo_catalogo', static::$catalogoType);
            }
        });
    }

    /**
     * Sobrescribe el método create para asignar automáticamente el tipo de catálogo.
     */
    public static function create(array $attributes = [])
    {
        if (static::$catalogoType && !isset($attributes['tipo_catalogo'])) {
            $attributes['tipo_catalogo'] = static::$catalogoType;
        }

        return parent::create($attributes);
    }

    /**
     * Relación: Un registro de catálogo pertenece a una Sucursal/Punto de Venta SIAT específico.
     * * @return BelongsTo
     */
    public function siatSucursalPuntoVenta(): BelongsTo
    {
        return $this->belongsTo(SiatSucursalPuntoVenta::class, 'siat_spv_id');
    }
}
