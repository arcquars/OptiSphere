<?php

namespace App\Services;

use App\DTOs\InvoiceCreationDto;
use App\Interfaces\MonoInvoiceApiInterface;
use App\Models\Branch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente HTTP dedicado a la API de MonoInvoices.
 */
class MonoInvoiceApiService implements MonoInvoiceApiInterface
{
    protected string $baseUrl;
    protected string $token;

    protected Branch $branch;

    /**
     * @param Branch $branch
     */
    public function __construct(Branch $branch)
    {
        $this->branch = $branch;
        $this->baseUrl = config('amyr.base_url'); 
        $this->token = $this->branch->amyrConnectionBranch->token;
    }

    /**
     * @inheritDoc
     */
    public function createInvoice(InvoiceCreationDto $invoiceData): ?array
    {
        $endpoint = '/invoices';
        $fullUrl = $this->baseUrl . $endpoint;

        Log::info("Enviando factura a MonoInvoices", [
                'endpoint' => $fullUrl,
                'payload_summary' => ['nitEmisor' => $invoiceData->nitRucNif, 'items' => count($invoiceData->items)]
            ]);

            Log::info("Enviando 2 factura a MonoInvoices", $invoiceData->toArray());

            $response = Http::baseUrl($this->baseUrl)
                            ->withToken($this->token) // Adjunta el Bearer Token
                            ->acceptJson()
                            ->timeout(120) // Tiempo de espera de 120 segundos
                            ->post($endpoint, $invoiceData->toArray()); // Usa el DTO convertido a array

            // 1. Manejo de Respuesta Exitosa (2xx)
            if ($response->successful()) {
                return $response->json();
            }

            $errorJson = $response->json();
            // 2. Manejo de Errores del Servidor o del Cliente (4xx, 5xx)
            Log::error("MonoInvoice API Error al crear factura.", [
                'status' => $response->status(),
                'response_body' => $errorJson,
                'request_payload' => $invoiceData->toArray(),
            ]);
            
            throw new \Exception("MonoInvoice API devolvió un error (Mensaje: " . $errorJson['error'] . ")", $response->status());
    }

    /**
     * @inheritDoc
     */
    public function voidInvoice($invoiceId, $motivo): ?array
    {
        $endpoint = DIRECTORY_SEPARATOR.'invoices' . DIRECTORY_SEPARATOR . $invoiceId . DIRECTORY_SEPARATOR . 'void';
        $fullUrl = $this->baseUrl . $endpoint;

        Log::info("Enviando factura a anular MonoInvoices", [
            'endpoint' => $fullUrl,
        ]);

        $response = Http::baseUrl($this->baseUrl)
                        ->withToken($this->token) // Adjunta el Bearer Token
                        ->acceptJson()
                        ->timeout(120) // Tiempo de espera de 120 segundos
                        ->post($endpoint, ['invoice_id' => $invoiceId, 'motivo_id' => $motivo]); // Usa el DTO convertido a array

        // 1. Manejo de Respuesta Exitosa (2xx)
        if ($response->successful()) {
            return $response->json();
        }

        $errorJson = $response->json();
        // 2. Manejo de Errores del Servidor o del Cliente (4xx, 5xx)
        Log::error("MonoInvoice API Error al Anular factura.", [
            'status' => $response->status(),
            'response_body' => $errorJson,
        ]);
        
        throw new \Exception("MonoInvoice API devolvió un error (Mensaje: " . $errorJson['error'] . ")", $response->status());
    }

    /**
     * @inheritDoc
     */
    public function pdfInvoice($invoiceId): ?array
    {
        $endpoint = DIRECTORY_SEPARATOR.'invoices' . DIRECTORY_SEPARATOR . $invoiceId . DIRECTORY_SEPARATOR . 'pdf';
        $fullUrl = $this->baseUrl . $endpoint;

        try {
            Log::info("Enviando factura pdf MonoInvoices", [
                'endpoint' => $fullUrl,
            ]);

            $response = Http::baseUrl($this->baseUrl)
                            ->withToken($this->token) // Adjunta el Bearer Token
                            ->acceptJson()
                            ->timeout(120) // Tiempo de espera de 120 segundos
                            ->get($endpoint, ['invoice_id' => $invoiceId, 'motivo_id' =>1]); // Usa el DTO convertido a array

            // 1. Manejo de Respuesta Exitosa (2xx)
            if ($response->successful()) {
                $data = $response->json();
                
                return $data['data'] ?? null; 
            }

            // 2. Manejo de Errores del Servidor o del Cliente (4xx, 5xx)
            Log::error("MonoInvoice API Error al pdf factura.", [
                'status' => $response->status(),
                'response_body' => $response->body(),
                'request_payload' => ['invoice_id' => $invoiceId, 'motivo_id' =>1],
            ]);
            
            // Lanza una excepción específica si es necesario, o devuelve null
            return null;

        } catch (\Exception $e) {
            // 3. Manejo de Errores de Conexión (cURL, timeouts, etc.)
            Log::critical("MonoInvoice API EXCEPCIÓN al PDF factura: " . $e->getMessage(), [
                'full_url' => $fullUrl
            ]);
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function verificarOnlineSiat(): bool|null
    {
        $endpoint = DIRECTORY_SEPARATOR.'invoices' . DIRECTORY_SEPARATOR . 'siat' . DIRECTORY_SEPARATOR . 'v2' . DIRECTORY_SEPARATOR . 'check-online-status';
        $fullUrl = $this->baseUrl . $endpoint;

        try {
            Log::info("Enviando factura pdf MonoInvoices", [
                'endpoint' => $fullUrl,
            ]);

            $response = Http::baseUrl($this->baseUrl)
                            ->withToken($this->token) // Adjunta el Bearer Token
                            ->acceptJson()
                            ->timeout(120) // Tiempo de espera de 120 segundos
                            ->get($endpoint); // Usa el DTO convertido a array

            // 1. Manejo de Respuesta Exitosa (2xx)
            if ($response->successful()) {
                $data = $response->json();
                
                return strcmp($data['response'], 'ok') == 0; 
                //return false;
            }

            return false;

        } catch (\Exception $e) {
            // 3. Manejo de Errores de Conexión (cURL, timeouts, etc.)
            Log::critical("MonoInvoice API EXCEPCIÓN al PDF factura: " . $e->getMessage(), [
                'full_url' => $fullUrl
            ]);
            return false;
        }
    }
}