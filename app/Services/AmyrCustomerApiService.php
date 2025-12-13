<?php

namespace App\Services;

use App\DTOs\CustomerSiatDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para interactuar con la API de AMYR.
 * Encapsula la configuración de la URL base y el manejo de peticiones.
 */
class AmyrCustomerApiService
{
    protected string $baseUrl;
    protected ?string $token = null;

    public function __construct($token)
    {
        // Carga la URL base desde el archivo de configuración
        $this->baseUrl = config('amyr.base_url');
        $this->token = $token;
    }
    

    // /**
    //  * Establece el token de autenticación para las peticiones subsiguientes.
    //  * @param string $token
    //  * @return $this
    //  */
    // public function withToken(string $token): self
    // {
    //     $this->token = $token;
    //     return $this;
    // }

    /**
     * Construye y realiza una petición POST para la autenticación.
     * @param CustomerSiatDto $customerDto
     * @return array|null Datos del usuario y token, o null en caso de fallo.
     */
    public function create(CustomerSiatDto $customerDto): ?array
    {
        $endpoint = 'customers'; 
        $fullUrl = $this->baseUrl . '/' . $endpoint;

        Log::info("Amyr API datas", $customerDto->toArray());
        try {
            $response = Http::baseUrl($this->baseUrl)
                            ->acceptJson()
                            ->withToken($this->token)
                            ->post($endpoint, $customerDto->toArray());

            if ($response->successful()) {
                $data = $response->json('data');
                Log::info("Amyr API Customers Create success....", [
                    'data' => $data['customer'],
                ]);
                return $data['customer'];
            }

            // Si falla la petición (e.g., 401 Unauthorized, 500 Server Error)
            Log::error("Amyr API Customers Create failed.", [
                'full_url' => $fullUrl,
                'status' => $response->status(),
                'response_body' => $response->body(),
                'reason' => 'Server returned an customers Create error on a token generation endpoint.'
            ]);

            return null;

        } catch (\Exception $e) {
            // Este bloque captura errores de conexión (cURL) o internos de PHP
            Log::error("Amyr API Customers Create Exception at {$fullUrl}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Construye y realiza una petición POST para la autenticación.
     * @param array $customerUpdate
     * @return array|null Datos del usuario y token, o null en caso de fallo.
     */
    public function update(array $customerUpdate): ?array
    {
        $endpoint = 'customers'; 
        $fullUrl = $this->baseUrl . '/' . $endpoint;

        Log::info("Amyr API datas", $customerUpdate);
        try {
            $response = Http::baseUrl($this->baseUrl)
                            ->acceptJson()
                            ->withToken($this->token)
                            ->put($endpoint, $customerUpdate);

            if ($response->successful()) {
                $data = $response->json('data');
                return $data;
            }

            // Si falla la petición (e.g., 401 Unauthorized, 500 Server Error)
            Log::error("Amyr API Customers Update failed.", [
                'full_url' => $fullUrl,
                'status' => $response->status(),
                'response_body' => $response->body(),
                'reason' => 'Server returned an customers Update error on a token generation endpoint.'
            ]);

            return null;

        } catch (\Exception $e) {
            // Este bloque captura errores de conexión (cURL) o internos de PHP
            Log::error("Amyr API Customers Update Exception at {$fullUrl}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Construye y realiza una petición POST para la autenticación.
     * @param string $nitRucNif
     * @return array|null Datos del usuario y token, o null en caso de fallo.
     */
    public function searchByNit($nitRucNif): ?array
    {
        $endpoint = 'customers/search-by-nit'; 
        $fullUrl = $this->baseUrl . '/' . $endpoint;

        try {
            $response = Http::baseUrl($this->baseUrl)
                            ->acceptJson()
                            ->withToken($this->token)
                            ->get($endpoint, ['keyword' => $nitRucNif]);

            Log::info("Amyr API Customers searchByNit response", [
                'status' => $response->status(),
                'body' => $response,
                'nitRucNif' => $nitRucNif
            ]);
            if ($response->successful()) {
                $data = $response->json('data');
                
                if (isset($data['customer_id'])) {
                    return $data;
                } else {
                    return null;
                }
            }

            // Si falla la petición (e.g., 401 Unauthorized, 500 Server Error)
            Log::error("Amyr API Customers failed.", [
                'full_url' => $fullUrl,
                'status' => $response->status(),
                'response_body' => $response->body(),
                'reason' => 'Server returned an customers error on a token generation endpoint.'
            ]);

            return null;

        } catch (\Exception $e) {
            // Este bloque captura errores de conexión (cURL) o internos de PHP
            Log::error("Amyr API Customers Exception at {$fullUrl}: " . $e->getMessage());
            return null;
        }
    }

}