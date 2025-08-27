<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    const TYPE_NORMAL = "normal";
    const TYPE_ESPECIAL = "especial";
    const TYPE_MAYORISTA = "mayorista";

    protected $fillable = [
        'name',
        'nit',
        'address',
        'email',
        'phone',
        'contact_info',
        'can_buy_on_credit',
        'type'
    ];
}
