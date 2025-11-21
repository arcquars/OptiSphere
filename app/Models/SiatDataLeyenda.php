<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiatDataLeyenda extends Model
{
    use HasFactory;
    protected $table = "siat_data_leyendas";

    protected $fillable = [
        "codigo_actividad",
        "descripcion_leyenda",
        "siat_spv_id",
    ];

    public function siatSucursalPuntoVenta(): BelongsTo
    {
        return $this->belongsTo(SiatSucursalPuntoVenta::class, 'siat_spv_id');
    }
}
