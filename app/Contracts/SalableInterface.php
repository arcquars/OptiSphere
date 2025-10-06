<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface SalableInterface
{
    /**
     * Define la relación polimórfica para los precios.
     */
    public function prices(): MorphMany;

    /**
     * Define la relación polimórfica para las promociones.
     */
    public function promotions(): MorphToMany;

    /**
     * Obtiene el precio base del ítem basado en la sucursal y el tipo de cliente.
     *
     * @param int|null $branchId ID de la sucursal (null para precio base global/almacén).
     * @param string $priceType Tipo de precio ('normal', 'especial', etc.).
     * @return float El precio encontrado o 0.0 si no existe.
     */
    public function getPriceByType(int $branchId = null, string $priceType = 'normal'): float;
}
