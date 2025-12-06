<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio dedicado a consumir los diferentes APIs de Catálogos de AMYR.
 * Requiere un token de autenticación previamente obtenido.
 */
class AmyrCatalogsService
{
    protected string $baseUrl;
    protected string $token;
    protected string $apiVersion = 'v1.0.0';

    /**
     * Constructor del servicio.
     * @param string $token El token de autenticación de la API de AMYR.
     */
    public function __construct(string $token='')
    {
        // Carga la URL base desde la configuración
        $this->baseUrl = config('amyr.base_url');
        // Almacena el token para usarlo en todas las peticiones
        $this->token = $token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }   

    /**
     * Método genérico para obtener datos de cualquier catálogo.
     * * @param string $catalogName El nombre del catálogo en la URL (ej: 'documento-tipo', 'productos-servicios').
     * @param array $queryParams Parámetros GET opcionales para filtrar o paginar.
     * @return array|null Los datos del catálogo o null en caso de fallo.
     */
    public function getCatalog(string $catalogName, array $queryParams = []): ?array
    {
        // Construye el endpoint completo del catálogo
        $endpoint = "/invoices/siat/v2/{$catalogName}";
        $fullUrl = $this->baseUrl . '/' . $endpoint;

        try {
            // Realiza la petición GET con el token y los parámetros de consulta
            $response = Http::baseUrl($this->baseUrl)
                            ->withToken($this->token) // Usa el token para la autorización
                            ->acceptJson()
                            ->get($endpoint, $queryParams);

            // Manejo de errores
            if ($response->successful()) {
                // Asumiendo que la respuesta exitosa tiene el formato esperado
                $data = $response->json('data');
                
                // Si 'data' está presente y es un array, lo devolvemos
                if (is_array($data)) {
                    return $data;
                }
            }
            
            // Si la respuesta no fue exitosa o no tiene el formato esperado
            Log::error("Amyr API Catalog request failed.", [
                'full_url' => $fullUrl,
                'catalog' => $catalogName,
                'status' => $response->status(),
                'response_body' => $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            // Error de conexión o cURL
            Log::error("Amyr API Catalog Exception at {$fullUrl}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Método específico para obtener el catálogo de Tipos de Documento.
     * @return array|null
     */
    public function getActividades(): ?array
    {
        return $this->getCatalog('actividades');
    }

    /**
     * Método específico para obtener el catálogo de Tipos de Documento.
     * @return array|null
     */
    public function getDocumentTypes(): ?array
    {
        return $this->getCatalog('documento-tipo');
    }

    /**
     * Método específico para obtener el catálogo de Productos y Servicios.
     * @return array|null
     */
    public function getProductsAndServices(): ?array
    {
        // Si este catálogo permite paginación o filtros, se pueden pasar aquí:
        // return $this->getCatalog('productos-servicios', ['page' => 1, 'limit' => 50]);
        return $this->getCatalog('productos-servicios');
    }
    
    // Puedes agregar más métodos específicos para cada catálogo que necesites (ej: Unidades de Medida, Paises, etc.)
}