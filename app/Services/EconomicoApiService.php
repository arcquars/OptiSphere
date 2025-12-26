<?php

namespace App\Services;

use App\DTOs\PaymentQr;
use App\Models\ConfiguracionBanco; // Importamos tu modelo
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Servicio para la integración con API Market de Banco Económico.
 * Ahora obtiene las credenciales dinámicamente de la base de datos.
 */
class EconomicoApiService
{
    protected string $baseUrl;
    protected string $userName;
    protected string $password;
    protected string $accountNumber;
    protected string $apiKey;
    protected string $companyCode;
    protected ?string $branchCode = null;

    // Endpoints relativos
    const ENDPOINT_ENCRYPT = 'api/authentication/encrypt';
    const ENDPOINT_AUTH = 'api/authentication/authenticate';
    const ENDPOINT_GENERATE_QR = 'api/qrsimple/generateQR';
    const ENDPOINT_CHECK_QR = 'api/qrsimple/v2/statusQR';
    const ENDPOINT_LIST_PAID_QR = 'api/qrsimple/v2/paidQR';

    /**
     * @throws Exception Si no hay una configuración bancaria activa.
     */
    public function __construct()
    {
        // 1. La URL base sigue siendo configuración de entorno (Dev vs Prod)
        $this->baseUrl = config('services.baneco.base_url');

        if (empty($this->baseUrl)) {
            throw new Exception('La URL base de Banco Económico no está configurada en services.php.');
        }

        // 2. Obtener credenciales de la Base de Datos
        // Usamos el scope 'activo' que definiste en tu modelo
        $configBanco = ConfiguracionBanco::activo()->first();

        if (!$configBanco) {
            throw new Exception('No se encontró ninguna configuración bancaria activa en la tabla configuracion_bancos.');
        }

        // Mapeamos las propiedades del modelo a las del servicio
        // NOTA: Gracias a los Accessors en tu modelo, 'password' y 'numero_cuenta'
        // se desencriptan automáticamente aquí.
        $this->userName = $configBanco->user_name;
        $this->password = $configBanco->password; 
        $this->accountNumber = $configBanco->numero_cuenta;
        $this->apiKey = $configBanco->api_key;
        $this->companyCode = $configBanco->codigo_empresa;
        
        // Si en el futuro agregas sucursal al modelo, mapealo aquí.
        // $this->branchCode = $configBanco->branch_code ?? null;
    }

    /**
     * Genera un código QR de pago.
     *
     * @param float $amount Monto a cobrar.
     * @param string $description Glosa o descripción del pago.
     * @param string $currency Moneda (BOB por defecto).
     * @param string|null $dueDate Fecha de vencimiento (Y-m-d). Si es null, usa hoy.
     * @param bool $singleUse Si el QR es de un solo uso.
     * @return PaymentQr Objeto con los datos del QR generado (ID, Imagen Base64, etc).
     * @throws Exception
     */
    public function generateQr(float $amount, string $description, string $currency = 'BOB', ?string $dueDate = null, bool $singleUse = true): PaymentQr
    {
        $token = $this->authenticate();

        // Encriptar el número de cuenta (Requisito de la API)
        $encryptedAccount = $this->encryptData($this->accountNumber, $token);

        $transactionId = 'tx_' . bin2hex(random_bytes(8));
        $dueDate = $dueDate ?? date('Y-m-d');

        $payload = [
            'transactionId' => $transactionId,
            'accountCredit' => $encryptedAccount,
            'currency' => $currency,
            'amount' => (float) number_format($amount, 2, '.', ''),
            'description' => $description,
            'dueDate' => $dueDate,
            'singleUse' => $singleUse,
            'modifyAmount' => false,
        ];

        if ($this->branchCode) {
            $payload['branchCode'] = $this->branchCode;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post($this->baseUrl . self::ENDPOINT_GENERATE_QR, $payload);

            if ($response->successful() && ($response->json('responseCode') === 0)) {
                $data = $response->json();
                $responseData = $data['body'] ?? $data; 
                $responseData['transactionId'] = $responseData['transactionId'] ?? $transactionId;
                
                return PaymentQr::fromArray($responseData);
            }

            Log::error('Baneco API Error (GenerateQR): ' . $response->body());
            throw new Exception('Error al generar el QR: ' . ($response->json('message') ?? 'Error desconocido'));

        } catch (Exception $e) {
            Log::error('Baneco Service Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica el estado de un QR.
     *
     * @param string $qrId Identificador del QR.
     * @return array Datos del estado y pagos realizados si existen.
     */
    public function checkQrStatus(string $qrId): array
    {
        $token = $this->authenticate();

        $response = Http::withToken($token)
            ->post($this->baseUrl . self::ENDPOINT_CHECK_QR, [
                'qrId' => $qrId
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Baneco API Error (CheckStatus): ' . $response->body());
        return ['success' => false, 'message' => 'Error al consultar estado'];
    }

    // --- Métodos Privados ---

    /**
     * Obtiene el token de autenticación.
     */
    private function authenticate(): string
    {
        // Cacheamos el token usando el ID de la empresa para evitar colisiones si hay múltiples
        $cacheKey = 'baneco_token_' . $this->companyCode;

        return Cache::remember($cacheKey, 3000, function () {
            
            $payload = [
                'login' => $this->userName,
                'password' => $this->password, // El modelo ya nos dio la contraseña desencriptada
                'companyCode' => $this->companyCode, 
                'apiKey' => $this->apiKey
            ];

            $response = Http::post($this->baseUrl . self::ENDPOINT_AUTH, $payload);

            if ($response->successful() && ($response->json('responseCode') === 0)) {
                return $response->json('token');
            }
            
            throw new Exception('Error de autenticación con Banco Económico: ' . $response->body());
        });
    }

    /**
     * Encripta un dato sensible.
     */
    private function encryptData(string $data, string $token): string
    {
        $response = Http::withToken($token)
            ->post($this->baseUrl . self::ENDPOINT_ENCRYPT, [
                'data' => $data
            ]);

        if ($response->successful() && ($response->json('responseCode') === 0)) {
            return $response->json('result');
        }

        throw new Exception('Error al encriptar datos sensibles.');
    }
}