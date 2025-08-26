<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
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
