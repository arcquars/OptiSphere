<?php

namespace App\DAOs;

use \App\Interfaces\SiatDocumentoTipoDAO;
use App\Models\AmyrConnectionBranch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiatApiDocumentoTipoDAO extends SiatCatalog implements SiatDocumentoTipoDAO
{   
    public function __construct($branchId)
    {
        $this->amyrConnectionBranch = AmyrConnectionBranch::where('branch_id',$branchId)->first();
        $this->apiBaseUrl = config('amyr.base_url');
        $this->catalogName = 'actividades';
    }

    /**
     * Obtiene la lista de tipos de documentos de sector desde la API SIAT.
     *
     * @return array|null Un array de objetos o un array asociativo con la lista de códigos.
     */
    public function getTiposDocumentoSector(): ?array
    {
        try {
            $data = $this->getCatalog();
            if($data !== null){
                if (isset($data['RespuestaListaActividades']['listaActividades'])) {
                    return $data['RespuestaListaActividades']['listaActividades'];
                }
                Log::warning('SiatApiDocumentoTipoDAO: Estructura de respuesta inesperada.', ['response' => $data]);
                return null;
            }

            Log::error('SiatApiDocumentoTipoDAO: Solicitud fallida.');
            
            return null;

        } catch (\Exception $e) {
            // Loguear errores de conexión o excepciones generales
            Log::critical('SiatApiDocumentoTipoDAO: Error de conexión o excepción.', ['error' => $e->getMessage()]);
            return null;
        }
    }

    function getPluck(): Array
    {
        $pluck = [];
        $tiposDocumento = $this->getTiposDocumentoSector();
        if ($tiposDocumento) {
            foreach ($tiposDocumento as $tipo) {
                $pluck[$tipo['codigoCaeb']] = $tipo['descripcion'];
            }
        }
        Log::warning("Eeee: ", ['pluck' => $pluck]);
        return $pluck;
    }
    
}