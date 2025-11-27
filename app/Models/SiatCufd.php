<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiatCufd extends Model
{
    use HasFactory;

    protected $table = 'siat_cufds';

    protected $fillable = [
        'codigo',
        'codigo_control',
        'direccion',
        'fecha_vigencia',
        'siat_spv_id',
    ];

    protected $casts = [
        'fecha_vigencia' => 'datetime',
    ];
    
    /**
     * Relación: Un registro de catálogo pertenece a una Sucursal/Punto de Venta SIAT específico.
     * * @return BelongsTo
     */
    public function siatSucursalPuntoVenta(): BelongsTo
    {
        return $this->belongsTo(SiatSucursalPuntoVenta::class, 'siat_spv_id');
    }
}
