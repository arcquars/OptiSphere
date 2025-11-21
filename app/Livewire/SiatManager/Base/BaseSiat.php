<?php

namespace App\Livewire\SiatManager\Base;

use App\Models\SiatProperty;
use App\Models\SiatSucursalPuntoVenta;
use App\Services\SiatService;
use Livewire\Component;

abstract class BaseSiat extends Component
{
    public $branchId;
    public SiatProperty $siatProperty;
    public SiatSucursalPuntoVenta $siatSucursalPuntoVenta;
    
    // Esta propiedad es común, pero se llena de forma distinta en cada hijo
    public $items;

    /**
     * Inicialización común.
     * Busca la propiedad SIAT y el punto de venta activo.
     */
    public function mount($branchId)
    {
        $this->branchId = $branchId;

        $auxSiat = SiatProperty::where('branch_id', $branchId)->first();

        if ($auxSiat) {
            $this->siatProperty = $auxSiat;
            // Asumimos que esta relación existe y devuelve un objeto válido
            if ($this->siatProperty->siatSucursalPuntoVentaActive) {
                $this->siatSucursalPuntoVenta = $this->siatProperty->siatSucursalPuntoVentaActive;
                
                // Llamamos al método abstracto que cada hijo implementa a su manera
                $this->loadData();
            }
        }
    }

    /**
     * Método abstracto que obliga a los hijos a definir cómo cargar sus datos.
     */
    abstract public function loadData();

    /**
     * @param SiatService $siatService
     */
    abstract public function getItems(SiatService $siatService);
}