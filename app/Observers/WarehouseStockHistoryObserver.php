<?php

namespace App\Observers;

use App\Models\WarehouseStockHistory;
use Illuminate\Support\Arr;

class WarehouseStockHistoryObserver
{
    /**
     * Handle the WarehouseStockHistory "created" event.
     */
    public function created(WarehouseStockHistory $warehouseStockHistory): void
    {
        //
    }

    /**
     * Handle the WarehouseStockHistory "updated" event.
     */
    public function updated(WarehouseStockHistory $warehouseStockHistory): void
    {
        // 1. Obtener solo los campos que realmente cambiaron
        $cambios = $warehouseStockHistory->getChanges();
        
        // Ignoramos la columna updated_at para no saturar el log
        unset($cambios['updated_at']); 

        if (count($cambios) > 0) {
            // 2. Obtener los valores originales solo de las columnas que cambiaron
            $originales = Arr::only($warehouseStockHistory->getOriginal(), array_keys($cambios));

            // 3. Guardar el registro en la tabla audits
            $warehouseStockHistory->audits()->create([
                'old_values' => $originales,
                'new_values' => $cambios,
                'user_id'    => auth()->id(), // Si el usuario está logueado
            ]);
        }
    }

    /**
     * Handle the WarehouseStockHistory "deleted" event.
     */
    public function deleted(WarehouseStockHistory $warehouseStockHistory): void
    {
        //
    }

    /**
     * Handle the WarehouseStockHistory "restored" event.
     */
    public function restored(WarehouseStockHistory $warehouseStockHistory): void
    {
        //
    }

    /**
     * Handle the WarehouseStockHistory "force deleted" event.
     */
    public function forceDeleted(WarehouseStockHistory $warehouseStockHistory): void
    {
        //
    }
}
