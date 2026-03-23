<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\DTOs\PaymentQr;
use App\DTOs\PaymentQrDto;
use App\Models\PagoQr;
use App\Models\Sale; // Asegúrate de que este sea tu modelo de ventas o pagos
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class BanecoNotificationController extends Controller
{
    /**
     * Procesa la notificación de pago enviada por el Banco Económico.
     * Según documentación: POST con body {"Payment": { ... PaymentQR ... }}
     */
    public function notify(Request $request)
    {
        // 1. Log inicial para depuración (crítico en integraciones bancarias)
        Log::info('Baneco QR Notification Received Request all:', $request->all());

        try {
            // 2. Validar la estructura básica
            // Generalmente el banco envía el objeto dentro de "Payment" o "object"
            $data = $request->input('payment') ?? $request->input('object') ?? $request->all();
            Log::info('Baneco QR Notification Received Request Payment:', $request->input('payment'));
            if (empty($data)) {
                return response()->json([
                    'responseCode' => 1,
                    'message' => 'Estructura de petición inválida (Body vacío)'
                ], 400);
            }

            // 3. Mapear datos al DTO existente
            // PaymentQr::fromArray se encarga de normalizar los nombres de los campos
            $paymentData = PaymentQrDto::fromArray($data);

            // 4. Buscar el registro de la venta en la base de datos
            // Buscamos por qrId o transactionId (el ID único que generaste al crear el QR)
            $pagoQr = PagoQr::where('qr_id', $paymentData->qrId)->where('transaction_id', $paymentData->transactionId)
                        ->first();

            if (!$pagoQr) {
                Log::warning("Baneco Notify: Pago recibido para QR inexistente en DB local: " . $paymentData->qrId);
                // Respondemos con error al banco para que sepa que no lo procesamos
                return response()->json([
                    'responseCode' => 2,
                    'message' => 'QR no encontrado en el sistema local'
                ], 404);
            }

            // 5. Verificar si ya fue procesado para evitar duplicados (Idempotencia)
            if (strcmp($pagoQr->status, PagoQr::STATUS_PAID) == 0) {
                Log::info("Baneco Notify: Pago ya procesado anteriormente para la venta con qr_id: {$paymentData->qrId}");
                return response()->json([
                    'responseCode' => 0, // Respondemos éxito porque ya está pagado
                    'message' => 'Pago ya procesado anteriormente'
                ]);
            }

            // 6. Actualizar la venta dentro de una transacción
            DB::transaction(function () use ($pagoQr, $paymentData) {
                $pagoQr = PagoQr::where("qr_id", $paymentData->qrId)->first();
                $sale = $pagoQr->sales->first();
                // Actualizamos estado y guardamos la metadata del banco
                $pagoQr->update([
                    'qr_id' => $pagoQr->qr_id,
                    'amount' => $paymentData->amount,
                    'currency' => $paymentData->currency,
                    'status' => PagoQr::STATUS_PAID,
                    'payment_date' => $paymentData->paymentDate,
                    'payment_time' => $paymentData->paymentTime,
                    'sender_bank_code' => $paymentData->senderBankCode,
                    'sender_name' => $paymentData->senderName,
                    'sender_document_id' => $paymentData->senderDocumentId,
                    'sender_account' => $paymentData->senderAccount,
                    
                ]);
                
                
                if($sale && $sale->due_amount > 0){
                    $credirService = new CreditService();
                    $credirService->registerPayment(
                        $paymentData->amount,
                        $sale->due_amount,
                        $sale,
                        'QR',
                        Auth::id(),
                        'Se registro el pago a credito desde QR del Banco'
                    );
                    $pagoQr->sales()->updateExistingPivot($sale->id, [
                        'status' => PagoQr::STATUS_PAID,
                        'amount' => $paymentData->amount
                    ]);
                } else {
                    Log::info("Baneco Notify: Pago ya procesado anteriormente la venta esta completamente pagada con qr_id: {$paymentData->qrId}");
                    return response()->json([
                        'responseCode' => 0, // Respondemos éxito porque ya está pagado
                        'message' => "Baneco Notify: Pago ya procesado anteriormente la venta esta completamente pagada con qr_id: {$paymentData->qrId}"
                    ]);
                }
                // Aquí podrías disparar eventos adicionales (enviar email, imprimir ticket, etc.)
                // Event::dispatch(new SalePaid($sale));
            });

            Log::info("Baneco Notify: Qr {$pagoQr->id} actualizada a PAGADO exitosamente.");

            // 7. Respuesta de Éxito al Banco (responseCode 0 es obligatorio)
            return response()->json([
                'responseCode' => 0,
                'message' => 'Notificación procesada con éxito'
            ]);

        } catch (Exception $e) {
            Log::error('Baneco Notify Critical Error: ' . $e->getTraceAsString());
            
            return response()->json([
                'responseCode' => 99, // Código de error genérico
                'message' => 'Error interno al procesar la notificación: ' . $e->getMessage()
            ], 500);
        }
    }
}