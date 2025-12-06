<?php

namespace App\DAOs;

use App\Interfaces\SiatProductoServicioDAO;
use App\Models\AmyrConnectionBranch;
use Illuminate\Support\Facades\Log;

class SiatApiProductoServicioDAO extends SiatCatalog implements SiatProductoServicioDAO
{

    public function __construct($branchId)
    {
        $this->amyrConnectionBranch = AmyrConnectionBranch::find($branchId);
        $this->apiBaseUrl = config('amyr.base_url');
        $this->catalogName = 'lista-productos-servicios';
    }

    public function getProductosServicios(): ?array
    {
        try {
            $data = $this->getCatalog();
            if($data !== null){
                if (isset($data['RespuestaListaProductos']['listaCodigos'])) {
                    return $data['RespuestaListaProductos']['listaCodigos'];
                }
                Log::warning('SiatApiProductoServicioDAO: Estructura de respuesta inesperada.', ['response' => $data]);
                return null;
            }

            Log::error('SiatApiProductoServicioDAO: Solicitud fallida.');
            
            return null;

        } catch (\Exception $e) {
            // Loguear errores de conexión o excepciones generales
            Log::critical('SiatApiProductoServicioDAO: Error de conexión o excepción.', ['error' => $e->getMessage()]);
            return null;
        }
    }

    function getPluckByActividad($activity): Array
    {
        $pluck = [];
        $productos = $this->getProductosServicios();
        if ($productos) {
            foreach ($productos as $producto) {
                if(strcmp($producto['codigoActividad'], $activity) == 0){
                    $pluck[$producto['codigoProducto']] = $producto['descripcionProducto'];
                }
            }
        }
        return $pluck;
    }
}