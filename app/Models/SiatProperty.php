<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SiatProperty extends Model
{
    const ENVIRONMENT_ELECTRONICA_ENLINEA = 1;
	const ENVIRONMENT_COMPUTARIZADA_ENLINEA = 2;

    use HasFactory;

    protected $fillable = [
        "system_name",
        "system_code",
        "nit",
        "company_name",
        "modality",
        "environment",
        "city",
        "phone",
        "token",
        "cuis",
        "cuis_data",
        "print_size",
        "path_logo",
        "path_digital_signature",
        "is_actived",
        "is_validated",
        "branch_id"
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function siatSucursalPuntoVentas(): HasMany{
        return $this->hasMany(SiatSucursalPuntoVenta::class);
    }

    public function siatSucursalPuntoVentaActive(): HasOne{
        return $this->hasOne(SiatSucursalPuntoVenta::class)->where('active', true);
    }

}
