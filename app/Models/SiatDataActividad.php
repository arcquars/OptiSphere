<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiatDataActividad extends Model
{
    use HasFactory;
    protected $table = "siat_data_actividades";

    protected $fillable = [
        "nro",
        "codigo",
        "descripcion",
        "tipo",
        "siat_spv_id",
    ];


    // Para obtener las propiedades padre (siat_properties)
    public function siatSucursalPuntoVenta(): BelongsTo
    {
        return $this->belongsTo(SiatSucursalPuntoVenta::class, 'siat_spv_id');
    }
}