<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductAuthentication extends Model
{
    protected $fillable = [
        'product_id',
        'cliente',
        'fecha_compra',
        'frequent_customer_id',
        'is_authentication',
        'authentication_approved_date',
        'authentication_approved_by',
        // Receta óptica (Lejos)
        'od_sphere',
        'od_cylinder',
        'od_axis',
        'oi_sphere',
        'oi_cylinder',
        'oi_axis',
        'add',
        'dip',
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'is_authentication' => 'boolean',
        'authentication_approved_date' => 'datetime',
        'od_sphere' => 'float',
        'od_cylinder' => 'float',
        'od_axis' => 'integer',
        'oi_sphere' => 'float',
        'oi_cylinder' => 'float',
        'oi_axis' => 'integer',
        'add' => 'float',
        'dip' => 'float',
    ];

    /**
     * Producto autenticado.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Cliente frecuente (registro de customers) que registró la autenticación.
     */
    public function frequentCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'frequent_customer_id');
    }
}
