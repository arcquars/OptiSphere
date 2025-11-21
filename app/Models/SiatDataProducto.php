<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiatDataProducto extends Model
{
    use HasFactory;
    protected $table = "siat_data_productos";

    protected $fillable = [
        "codigo_actividad",
        "codigo_producto",
        "descripcion_producto",
        "siat_spv_id",
    ];

    public function siatSucursalPuntoVenta(): BelongsTo
    {
        return $this->belongsTo(SiatSucursalPuntoVenta::class, 'siat_spv_id');
    }
}
