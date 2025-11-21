<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiatDataActividadDocSector extends Model
{
    use HasFactory;
    protected $table = "siat_data_actividad_documento_sectores";

    protected $fillable = [
        "codigo_actividad",
        "codigo_documento_sector",
        "tipo_documento_sector",
        "siat_spv_id",
    ];


    // Para obtener las propiedades padre (siat_properties)
    public function siatSucursalPuntoVenta(): BelongsTo
    {
        return $this->belongsTo(SiatSucursalPuntoVenta::class, 'siat_spv_id');
    }
}
