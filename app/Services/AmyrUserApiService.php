<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para interactuar con la API de AMYR.
 * Encapsula la configuración de la URL base y el manejo de peticiones.
 */
class AmyrUserApiService
{
    protected string $baseUrl;
    protected ?string $token = null;

    public function __construct()
    {
        // Carga la URL base desde el archivo de configuración
        $this->baseUrl = config('amyr.base_url');
    }
    
    /**
     * Establece el token de autenticación para las peticiones subsiguientes.
     * @param string $token
     * @return $this
     */
    public function withToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Construye y realiza una petición POST para la autenticación.
     * @param string $username
     * @param string $password
     * @return array|null Datos del usuario y token, o null en caso de fallo.
     */
    public function authenticate(string $username, string $password): ?array
    {
        $endpoint = 'v1.0.0/users/get-token'; 
        $fullUrl = $this->baseUrl . '/' . $endpoint;

        try {
            // Enviamos los datos como JSON. Laravel ya agrega Content-Type: application/json.
            // Agregamos acceptJson() para asegurar que Accept: application/json también esté.
            $response = Http::baseUrl($this->baseUrl)
                            ->acceptJson()
                            ->post($endpoint, [
                                'username' => $username,
                                'password' => $password,
                            ]);

            // dd($this->baseUrl, $endpoint);
            // Manejo de errores
            if ($response->successful()) {
                $data = $response->json('data');
                
                if (isset($data['token']) && isset($data['user'])) {
                    $this->withToken($data['token']);
                    return $data;
                }
            }

            // Si falla la petición (e.g., 401 Unauthorized, 500 Server Error)
            Log::error("Amyr API Authentication failed.", [
                'full_url' => $fullUrl,
                'status' => $response->status(),
                'response_body' => $response->body(),
                'reason' => 'Server returned an authentication error on a token generation endpoint.'
            ]);

            return null;

        } catch (\Exception $e) {
            // Este bloque captura errores de conexión (cURL) o internos de PHP
            Log::error("Amyr API Authentication Exception at {$fullUrl}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Ejemplo de método para consumir un endpoint que requiere autenticación.
     * @param string $path El endpoint específico (ej: 'users/profile')
     * @return array|null
     */
    public function getResource(string $path): ?array
    {
        if (!$this->token) {
            Log::warning("Amyr API call attempted without token: " . $path);
            return null;
        }
        
        try {
            $response = Http::baseUrl($this->baseUrl)
                            ->withToken($this->token) // Usa el token almacenado
                            ->acceptJson()
                            ->get($path);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("Amyr API GET request failed.", [
                'path' => $path,
                'status' => $response->status(),
                'response_body' => $response->body(),
            ]);
            
            return null;

        } catch (\Exception $e) {
            Log::error("Amyr API GET Exception: " . $e->getMessage());
            return null;
        }
    }
}