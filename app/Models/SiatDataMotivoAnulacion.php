<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiatDataMotivoAnulacion extends SiatData
{
    public static ?string $catalogoType = 'motivo_anulacion';
}
