<?php

namespace App\Traits;

use App\Models\Price;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasPricesAndPromotions
{
    /**
     * Define la relación polimórfica para los precios (priceable).
     */
    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    /**
     * Define la relación polimórfica inversa para las promociones (promotionable).
     */
    public function promotions(): MorphToMany
    {
        // Asumiendo que existe una tabla pivote 'promotionables'
        return $this->morphToMany(Promotion::class, 'promotionable');
    }

    /**
     * Implementación de getPriceByType del SalableInterface.
     * Busca el precio específico para una sucursal y tipo de cliente.
     *
     * @param int|null $branchId ID de la sucursal.
     * @param string $priceType Tipo de precio (ej. 'normal').
     * @return float
     */
    public function getPriceByType(int $branchId = null, string $priceType = 'normal'): float
    {
        $price = $this->prices()
            ->where('branch_id', $branchId) // Busca el precio específico de la sucursal
            ->where('type', $priceType)
            ->first();

        return $price ? (float) $price->price : 0.0;
    }
}
