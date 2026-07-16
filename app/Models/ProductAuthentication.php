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
    ];

    protected $casts = [
        'fecha_compra' => 'date',
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
