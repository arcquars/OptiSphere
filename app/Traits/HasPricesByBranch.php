<?php

namespace App\Traits;

use App\Models\Price;

trait HasPricesByBranch
{
    /**
     * Obtiene el precio correcto para una sucursal específica,
     * con fallback al precio base si no existe uno específico.
     *
     * @param int|null $branchId El ID de la sucursal.
     * @param string $type El tipo de precio (normal, especial, etc.).
     * @return Price|null
     */
    public function getPriceForBranch(?int $branchId, string $type = Price::TYPE_NORMAL): ?Price
    {
        // 1. Intentar encontrar el precio específico para la sucursal y el tipo.
        $branchPrice = $this->prices()
            ->where('branch_id', $branchId)
            ->where('type', $type)
            ->first();

        // Si se encuentra, lo devolvemos.
        if ($branchPrice) {
            return $branchPrice;
        }

        // 2. Si no hay precio de sucursal, buscar el precio base (donde branch_id es NULL).
        return $this->prices()
            ->whereNull('branch_id')
            ->where('type', $type)
            ->first();
    }
}
