<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmyrConnectionBranch extends Model
{
    protected $fillable = [
        'amyr_user',
        'amyr_password',
        'sucursal',
        'point_sale',
        'is_actived',
        'token',
        'branch_id',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
