<?php

namespace App\Models;

use Amyrit\SiatBoliviaClient\Data\Responses\RespuestaCufd;
use App\Services\SiatCodigos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiatSucursalPuntoVenta extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "siat_sucursales_puntos_ventas";

    protected $fillable = [
        "sucursal",
        "punto_venta",
        "cuis",
        "cuis_date",
        "siat_property_id"
    ];

     protected $dates = ['cuis_date', 'deleted_at'];

    // --- CÓMO USARLO EN TU CÓDIGO ---

    // Para obtener las propiedades padre (siat_properties)
    public function siatProperty(): BelongsTo
    {
        // Asumiendo que has definido la llave foránea 'siat_property_id'
        return $this->belongsTo(SiatProperty::class, 'siat_property_id');
    }

    public function getSiatCufdActive(): SiatCufd|null
    {
        /**
         * @var SiatCufd $cufd
         */
        $cufd = SiatCufd::where('siat_spv_id', $this->id)
            ->where('fecha_vigencia', '>=', now())
            ->orderBy('fecha_vigencia', 'desc')
            ->first();
        return $cufd;
        
    }
    
}
