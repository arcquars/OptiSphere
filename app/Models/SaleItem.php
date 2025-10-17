<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'salable_id',
        'salable_type',
        'quantity',
        'base_price',
        'promotion_id',
        'promotion_discount_rate',
        'final_price_per_unit',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'float',
        'base_price' => 'float',
        'promotion_discount_rate' => 'float',
        'final_price_per_unit' => 'float',
        'subtotal' => 'float',
    ];

    protected $appends = ['is_service', 'type_label', 'total_with_services'];

    /**
     * Relación con el encabezado de la venta.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación polimórfica con el elemento vendido (Product o Service).
     */
    public function salable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Servicios adicionales vendidos junto con este SaleItem (ej: Instalación, Garantía).
     */
    public function attachedServices(): HasMany
    {
        return $this->hasMany(SaleItemService::class);
    }

    protected function isService(): Attribute
    {
        return Attribute::get(fn () => $this->salable instanceof Service);
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->is_service ? 'Servicio' : 'Producto');
    }

    /**
     * Relación con la promoción aplicada (opcional).
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Calcula el subtotal total de la línea, sumando el subtotal del ítem
     * principal ('subtotal') más el subtotal de todos los servicios adjuntos.
     */
    protected function totalWithServices(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Si la relación attachedServices ya está cargada, la usamos (eager loading)
                if ($this->relationLoaded('attachedServices')) {
                    $servicesTotal = $this->attachedServices->sum('subtotal');
                } else {
                    // Si no está cargada, hacemos la consulta.
                    // Nota: Es mejor usar Eager Loading (with('attachedServices')) para evitar N+1
                    $servicesTotal = $this->attachedServices()->sum('subtotal');
                }

                // Sumamos el subtotal base del SaleItem más el total de los servicios adjuntos.
                return $this->subtotal + $servicesTotal;
            }
        )->shouldCache();
    }
}
