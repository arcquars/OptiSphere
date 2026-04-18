<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    const BUSINESS_WAREHOUSE = 'ALMACEN';
    const BUSINESS_BRANCH = 'SUCURSAL';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'location',
        'is_active'
    ];

}
