<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiatDataEvento extends SiatData
{
    public static ?string $catalogoType = 'evento_significativo';
}
