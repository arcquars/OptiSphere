<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\PagoQr;
use App\Models\Sale;
use App\Models\SalePayment;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreditService
{
    /**
     * Registra un pago (abono) a una venta a crédito específica
     * y actualiza el saldo de la venta.
     *
     * @param Sale $sale La venta a la que se aplicará el pago.
     * @param float $amount El monto del pago/abono.
     * @param string $paymentMethod El método de pago (usando constantes de SalePayment).
     * @param int $userId El ID del usuario que registra el pago.
     * @param string|null $notes Notas opcionales sobre el pago.
     * @throws InvalidArgumentException Si el monto es inválido o excede el saldo pendiente.
     * @throws Exception Si la transacción de base de datos falla.
     * @return SalePayment El registro del pago recién creado.
     */
    public function registerPayment(
        float $amount,
        float $dueAmount,
        Sale $sale,
        string $paymentMethod,
        int $userId,
        ?string $notes = null
    ): SalePayment {
        // 1. Validaciones
        if ($amount <= 0) {
            throw new InvalidArgumentException("El monto del pago debe ser positivo.");
        }

        if ($sale->due_amount === null || $sale->due_amount < 0) {
            throw new InvalidArgumentException("Esta venta ya está completamente pagada o no es una venta a crédito.");
        }

        if ($amount > $dueAmount) {
            throw new InvalidArgumentException("El monto del pago ($amount) excede el saldo pendiente ({$sale->due_amount}).");
        }

        // 2. Ejecutar Transacción (Garantiza que ambas operaciones se completen o ninguna)
        return DB::transaction(function () use ($sale, $amount, $paymentMethod, $userId, $notes) {

            $branch = Branch::find($sale->branch_id);
            $cashBc = $branch->getCashBoxClosingByUser($userId);
            // a) Crear el registro del abono (SalePayment)
            $payment = SalePayment::create([
                'sale_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'user_id' => $userId,
                'qrid' => $sale->qrid,
                'cash_box_closing_id' => $cashBc?->id,
                'amount' => $amount,
                'residue' => $sale->final_total - ($sale->paid_amount + $amount),
                'payment_method' => $paymentMethod,
                'notes' => $notes,
            ]);

            // b) Actualizar la venta (Sale)
            $newPaidAmount = $sale->paid_amount + $amount;
            $newDueAmount = $sale->final_total - $newPaidAmount;

            $updateData = [
                'paid_amount' => $newPaidAmount,
                'due_amount' => max(0, $newDueAmount), // Asegurar que el saldo no sea negativo
            ];

            // Si el nuevo saldo pendiente es cero, actualizamos el estado de la venta.
            // if ($newDueAmount <= 0.001) { // Usar un margen de error para flotantes
            //     $updateData['status'] = Sale::SALE_STATUS_PAID;
            // }

            $sale->update($updateData);

            return $payment;
        });
    }

    /**
     * Reversa completamente un pago previamente registrado.
     * Esto también revierte la venta a su estado anterior al pago.
     *
     * @param SalePayment $payment El abono a revertir.
     * @throws InvalidArgumentException Si el pago ya fue revertido.
     * @throws Exception Si la transacción de base de datos falla.
     * @return bool
     */
    public function reversePayment(SalePayment $payment): bool
    {
        if ($payment->deleted_at !== null) {
            throw new InvalidArgumentException("Este pago ya fue revertido previamente.");
        }

        $sale = $payment->sale;
        $amount = $payment->amount;

        return DB::transaction(function () use ($payment, $sale, $amount) {

            // 1. Marcar el abono como eliminado (soft delete)
            $payment->delete(); // Usar soft deletes en SalePayment es una buena práctica

            // 2. Revertir los montos en la venta
            $newPaidAmount = $sale->paid_amount - $amount;
            $newDueAmount = $sale->final_total - $newPaidAmount;

            $updateData = [
                'paid_amount' => $newPaidAmount,
                'due_amount' => $newDueAmount,
            ];

            // Si la venta estaba en estado 'PAGADA' y ahora vuelve a tener saldo
            if ($sale->status === Sale::SALE_STATUS_PAID && $newDueAmount > 0) {
                $updateData['status'] = Sale::SALE_STATUS_CREDIT;
            }

            $sale->update($updateData);

            return true;
        });
    }

    public function getQrPartialPayment($saleId){
        $sale = Sale::with(['partialQrPayments' => function ($query) {
            $query->wherePivot('status', 'PENDING');
        }])->find($saleId);
        if(strcmp($sale->status, Sale::SALE_STATUS_CREDIT) != 0){
            throw new Exception("La venta no es a CREDITO.");
        }
        if(count($sale->partialQrPayments) > 0){
            $pagoQr = $sale->partialQrPayments->first();

            $fecha = new DateTime("now");
            $fecha->setTime(0, 0);
            $fechaVencimiento = (clone $pagoQr->payment_date)->setTime(0, 0);
            if($fechaVencimiento >= $fecha){
                if(strcmp($pagoQr->status, PagoQr::STATUS_PENDING) == 0){
                    return $pagoQr;
                }
            } else {
                $sale->partialQrPayments()->updateExistingPivot($pagoQr->id, ['status' => PagoQr::STATUS_EXPIRED]);
            }
        }
        $apiService = new EconomicoApiService($sale->branch_id);
        $dataExtra = [
            'cajero' => Auth::user()->name ?? 'Sistema',
            'sucursal' => $sale->name,
            'cliente' => $sale->customer->razon_social ?? 'SN'
        ];

        DB::transaction(function () use ($sale, $apiService, $dataExtra) {
            // Llamada al servicio
            $response = $apiService->generateQr(
                $sale->due_amount, 
                "Compra en " . $sale->branch->name,
                config('cerisier.currency_symbol', "BOB"), 
                null, 
                true,
                $dataExtra
            );
            $pagoQr = PagoQr::where('qr_id', $response->qrId)->first();
            $sale->partialQrPayments()->syncWithoutDetaching([$pagoQr->id => ['status' => 'PENDING', 'amount' => $sale->due_amount]]);
        });

        $sale = Sale::with(['partialQrPayments' => function ($query) {
            $query->wherePivot('status', 'PENDING');
        }])->find($saleId);

        return $sale->partialQrPayments->first();
    }
}
