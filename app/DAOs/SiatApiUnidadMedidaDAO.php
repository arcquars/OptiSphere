<?php

namespace App\DAOs;

use App\Interfaces\SiatUnidadeMedidaDAO;
use App\Models\AmyrConnectionBranch;
use Illuminate\Support\Facades\Log;

class SiatApiUnidadMedidaDAO extends SiatCatalog implements SiatUnidadeMedidaDAO
{

    public function __construct($branchId)
    {
        $this->amyrConnectionBranch = AmyrConnectionBranch::find($branchId);
        $this->apiBaseUrl = config('amyr.base_url');
        $this->catalogName = 'sync-unidades-medida';
    }

    public function getUnidadesMedida(): ?array
    {
        try {
            $data = $this->getCatalog();
            if($data !== null){
                if (isset($data['RespuestaListaParametricas']['listaCodigos'])) {
                    return $data['RespuestaListaParametricas']['listaCodigos'];
                }
                Log::warning('SiatApiUnidadMedidaDAO: Estructura de respuesta inesperada.', ['response' => $data]);
                return null;
            }

            Log::error('SiatApiUnidadMedidaDAO: Solicitud fallida.');
            
            return null;

        } catch (\Exception $e) {
            // Loguear errores de conexión o excepciones generales
            Log::critical('SiatApiUnidadMedidaDAO: Error de conexión o excepción.', ['error' => $e->getMessage()]);
            return null;
        }
    }

    function getPluck(): Array
    {
        $pluck = [];
        $unidades = $this->getUnidadesMedida();
        if ($unidades) {
            foreach ($unidades as $unidad) {
                $pluck[$unidad['codigoClasificador']] = $unidad['descripcion'];
            }
        }
        return $pluck;
    }
}