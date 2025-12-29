<?php

namespace App\Services;

use App\DTOs\PaymentQr;
use App\Models\Branch;
use App\Models\ConfiguracionBanco; // Importamos tu modelo
use App\Models\PagoQr;
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
    public function __construct($branchId)
    {
        // 1. La URL base sigue siendo configuración de entorno (Dev vs Prod)
        $this->baseUrl = config('services.baneco.base_url');

        if (empty($this->baseUrl)) {
            throw new Exception('La URL base de Banco Económico no está configurada en services.php.');
        }

        // 2. Obtener credenciales de la Base de Datos
        // Usamos el scope 'activo' que definiste en tu modelo
        $configBanco = Branch::find($branchId)->configuracionBanco;

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
        $authEcosnomico = $this->authenticate();

        if (!$authEcosnomico['success']) {
            throw new Exception('Error al generar el QR auth: ' . ($authEcosnomico['message']) ?? 'Error desconocido');
        }
        
        $token = $authEcosnomico['token'];
        $enc = $this->encryptData($this->accountNumber);
        if (!$enc['success']){
            throw new Exception('Error al generar el QR encript cuenta: ' . ($enc['message']) ?? 'Error desconocido');
        };
        
        $transactionId = 'tx_' . bin2hex(random_bytes(8));
        $dueDate = $dueDate ?? date('Y-m-d');

        $payload = [
            'transactionId' => $transactionId,
            'accountCredit' => $enc['data'],
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
            // $response = Http::withToken($token)
            //     ->timeout(30)
            //     ->post($this->baseUrl . self::ENDPOINT_GENERATE_QR, $payload);

            Log::info("www pppooo::::: " . json_encode($payload));
            $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])
            ->post($this->baseUrl . self::ENDPOINT_GENERATE_QR, $payload);

            if ($response->successful() && ($response->json('responseCode') === 0)) {
                $data = $response->json();
                Log::info("paymentQr data:: " . json_encode($data));
                $responseData = $data['body'] ?? $data; 
                $responseData['transactionId'] = $responseData['transactionId'] ?? $transactionId;
                
                $paymentQr = PaymentQr::fromArray($responseData);
                PagoQr::create([
                    'transaction_id' => $responseData['transactionId'],
                    'qr_id' => $paymentQr->qrId,
                    'amount' => $payload['amount'],
                    'currency' => $payload['currency'],
                    'description' => $payload['description'],
                    'branch_code' => null,
                    'status' => 1,
                    'payment_date' => $payload['dueDate'],
                    'qr_image' => $paymentQr->qrImage,
                    'extra_data' => null
                ]);
                return $paymentQr;
            }

            Log::error('Baneco API Error (GenerateQR): ' . json_encode($response));
            throw new Exception('Error al generar el QR Qr: ' . ($response->json('message') ?? 'Error desconocido'));

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

        try{
        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token['token'],
            ])
            ->get($this->baseUrl . self::ENDPOINT_CHECK_QR . DIRECTORY_SEPARATOR . urlencode($qrId));

        $data = $response->json();
        if (!$response->successful() || !isset($data['statusQrCode'])) {
            return ['success' => false, 'error' => $data['message'] ?? 'Error al verificar QR'];
        }

        $estado = $data['statusQrCode'];
        $fechaPago = $estado === 1 && !empty($data['payment'][0]['paymentDate'])
            ? substr($data['payment'][0]['paymentDate'], 0, 10)
            : null;

        if ($estado == 1) {
            PagoQr::updateOrCreate(
                ['qr_id' => $qrId],
                [
                    'transaction_id' => $data['transactionId'] ?? $qrId,
                    'qr_id' => $qrId,
                    'amount' => $data['payment'][0]['amount'] ?? 0,
                    'currency' => $data['payment'][0]['currency'] ?? 'BOB',
                    'description' => $data['description'] ?? null,
                    'branch_code' => $data['branchCode'] ?? null,
                    'status' => 1,
                    'payment_date' => $fechaPago,
                    'qr_image' => null,
                    'extra_data' => $data,
                ]
            );
        }

        return [
                'success' => true,
                'qrId' => $qrId,
                'estado' => $estado,
                'fechaPago' => $fechaPago,
                'message' => $data['message'] ?? null,
                'body' => $data
            ];
        } catch (Exception $e) {
            Log::error('Error verificarQr: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // --- Métodos Privados ---

    /**
     * Obtiene el token de autenticación.
     */
    private function authenticate(): array
    {
        // Cacheamos el token usando el ID de la empresa para evitar colisiones si hay múltiples
        $cacheKey = 'baneco_token_' . $this->companyCode;

        // Encriptar el número de cuenta (Requisito de la API)
        $encryptedAccount = $this->encryptData($this->password);

        return Cache::remember($cacheKey, 3000, function () use ($encryptedAccount) {
            
            $payload = [
                'userName' => $this->userName,
                'password' => $encryptedAccount['data'],
            ];

            $response = Http::post($this->baseUrl . self::ENDPOINT_AUTH, $payload);
            $data = $response->json();
            if ($response->successful() && ($data['responseCode'] ?? -1) == 0 && isset($data['token'])) {
                return ['success' => true, 'token' => $data['token']];
            }   

            return ['success' => false, 'message' => $data['message'] ?? 'Error autenticación'];
        });
    }

    /**
     * Encripta un dato sensible.
     */
    private function encryptData($text): array
    {
        // $response = Http::withToken($token)
        //     ->post($this->baseUrl . self::ENDPOINT_ENCRYPT, [
        //         'data' => $data
        //     ]);

        // if ($response->successful() && ($response->json('responseCode') === 0)) {
        //     return $response->json('result');
        // }

        // throw new Exception('Error al encriptar datos sensibles.');

        $response = Http::timeout(30)
            ->get($this->baseUrl . self::ENDPOINT_ENCRYPT, [
                'text' => $text,
                'aesKey' => $this->apiKey
            ]);

        Log::info("WWW pppp bbbb:: " . $response->body());
        if ($response->successful()) {
            return ['success' => true, 'data' => preg_replace('/[^A-Za-z0-9\+\/\=]/', '', trim($response->body()))];
        }

        throw new Exception('Error al encriptar datos sensibles.');
    }

    // private function encriptar($texto)
    // {
    //     $res = Http::timeout(30)
    //         ->get($this->baseUrl . self::ENDPOINT_ENCRYPT . '?text=' . urlencode($texto) . '&aesKey=' . $this->apiKey);

    //     if ($res->successful()) {
    //         return ['success' => true, 'data' => preg_replace('/[^A-Za-z0-9\+\/\=]/', '', trim($res->body()))];
    //     }
    //     return ['success' => false, 'message' => $res->body()];
    // }
}