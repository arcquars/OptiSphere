<?php

namespace App\DAOs;

use App\Models\AmyrConnectionBranch;
use Illuminate\Support\Facades\Http;
use Log;

class SiatCatalog
{
    protected ?AmyrConnectionBranch $amyrConnectionBranch;
    protected string $apiBaseUrl;

    protected $catalogName;

    public function getApiBaseUrl(): string
    {
        return $this->apiBaseUrl;
    }

    /**
     * Método genérico para obtener datos de cualquier catálogo.
     * * @param string $catalogName El nombre del catálogo en la URL (ej: 'documento-tipo', 'productos-servicios').
     * @param array $queryParams Parámetros GET opcionales para filtrar o paginar.
     * @return array|null Los datos del catálogo o null en caso de fallo.
     */
    public function getCatalog(): ?array
    {
        Log::info("ddd 1.1");
        // Construye el endpoint completo del catálogo
        $endpoint = "invoices/siat/v2/{$this->catalogName}";
        Log::info("ddd 1.2");
        $fullUrl = $this->apiBaseUrl . '/' . $endpoint;

        Log::info("Amyr API Catalog request to {$this->amyrConnectionBranch->token}");
        Log::info("Amyr API URL {$this->apiBaseUrl}");
        Log::info("Amyr API Endpoint {$endpoint}");
        try {
            // Realiza la petición GET con el token y los parámetros de consulta
            $response = Http::baseUrl($this->apiBaseUrl)
                            ->withToken($this->amyrConnectionBranch->token)
                            ->acceptJson()
                            ->get($endpoint);

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
                'catalog' => $this->catalogName,
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
}