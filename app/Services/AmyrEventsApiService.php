<?php

namespace App\Services;

use App\DTOs\CustomerSiatDto;
use App\DTOs\EventSiatDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para interactuar con la API de AMYR.
 * Encapsula la configuración de la URL base y el manejo de peticiones.
 */
class AmyrEventsApiService
{
    protected string $baseUrl;
    protected ?string $token = null;

    public function __construct($token)
    {
        // Carga la URL base desde el archivo de configuración
        $this->baseUrl = config('amyr.base_url');
        $this->token = $token;
    }
    

    /**
     * @param EventSiatDto $eventData
     * @return array|null Datos del evento creado, o null en caso de fallo.
     */
    public function createEvent(EventSiatDto $eventData): ?array
    {
        $endpoint = 'invoices' . DIRECTORY_SEPARATOR . 'siat' . DIRECTORY_SEPARATOR . 'v2' . DIRECTORY_SEPARATOR . 'eventos'; 
        $fullUrl = $this->baseUrl . DIRECTORY_SEPARATOR . $endpoint;

        $response = Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->withToken($this->token)
            ->post($endpoint, $eventData->toArray());

        if ($response->successful()) {
            $data = $response->json('data');
            return $data;
        }

        Log::error("Amyr API Events Create failed.", [
            'status' => $response->status(),
            'response' => $response->body(),
        ]);
        throw new \Exception($response->json('error') ?? 'Desconocido', $response->json('code'));
    }

    /**
     * Construye y realiza una petición POST para la autenticación.
     * @param int $eventId
     * @return array|null Datos del evento creado, o null en caso de fallo.
     */
    public function closeEvent($eventId):?array
    {
        $endpoint = 'invoices'. DIRECTORY_SEPARATOR .
            'siat' . DIRECTORY_SEPARATOR . 
            'v2' . DIRECTORY_SEPARATOR . 
            'eventos' . DIRECTORY_SEPARATOR . 
            $eventId . DIRECTORY_SEPARATOR . 'cerrar';

        $fullUrl = $this->baseUrl . DIRECTORY_SEPARATOR . $endpoint;
        Log::info("sssss kkkkk:: " . $fullUrl);
        $response = Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->withToken($this->token)
            ->get($endpoint);
        if ($response->successful()) {
            return [
                'response' => $response->json('response'),
                'message' => $response->json('message'),
                'code' => $response->json('code'),
                'data' => $response->json('data')
            ];
        }
        Log::error("Error pdm: " . $response->body());
        throw new \Exception($response->json('error') ?? 'Desconocido', $response->json('code'));
        
    }

    public function getEventActive($pointSale): ?array
    {
        $endpoint = 'invoices'. DIRECTORY_SEPARATOR .
            'siat' . DIRECTORY_SEPARATOR . 
            'v2' . DIRECTORY_SEPARATOR . 
            'eventos' . DIRECTORY_SEPARATOR . 'activo'; 
        $fullUrl = $this->baseUrl . DIRECTORY_SEPARATOR . $endpoint;

        try {
            $response = Http::baseUrl($this->baseUrl)
                            ->acceptJson()
                            ->withToken($this->token)
                            ->get($endpoint, ['puntoventa' => $pointSale]);

            if ($response->successful()) {
                $data = $response->json('data');
                Log::info("Amyr API Events Get Active success....", [
                    'data' => $data,
                ]);
                return $data;
            }

            Log::error("Amyr API Events Get Active failed.", [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error("Amyr API Events Get Active Exception: " . $e->getMessage());
            return null;
        }
    }

}