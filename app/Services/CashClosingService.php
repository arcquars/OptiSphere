<?php

namespace App\Services;

use App\Models\CashBoxClosing;
use App\Models\CashMovement;
use App\Models\Sale;
use App\Models\SalePayment;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashClosingService
{
    public function userCanClose(Authenticatable $user): bool
    {
        return $user?->hasRole('admin') || $user?->hasRole('branch-manager');
    }

    /**
     * Obtiene o crea (opcional) el cierre abierto del usuario.
     */
    public function getOpenClosingForUser(int $userId, int $branchId, bool $createIfMissing = false): ?CashBoxClosing
    {
        $closing = CashBoxClosing::query()
            ->where('user_id', $userId)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if (! $closing && $createIfMissing) {
            $closing = CashBoxClosing::create([
                'user_id'        => $userId,
                'branch_id'      => $branchId,
                'opening_time'      => now(),
                'initial_balance' => 0, // si tienes “monto de apertura” pídeselo al usuario en UI
                'status'         => 'open',
            ]);
        }

        return $closing;
    }

    /**
     * Totales del cierre en el rango dado.
     *
     * @return array{
     *  sales: array{cash: float, transfer: float, qr: float},
     *  credit_payments: array{cash: float, transfer: float, qr: float},
     *  movements: array{incomes: float, expenses: float},
     *  system_total: float
     * }
     */
    public function computeTotals(CashBoxClosing $closing, ?string $from = null, ?string $until = null, ?int $userIdFilter = null): array
    {
        $from  = $from  ? Carbon::parse($from)  : Carbon::parse($closing->opening_time);
        $until = $until ? Carbon::parse($until) : now();

        // Base de filtros por rango/usuario
        $range = static function (Builder $q) use ($from, $until, $userIdFilter) {
            $q->whereBetween('created_at', [$from, $until]);
            if ($userIdFilter) {
                $q->where('user_id', $userIdFilter);
            }
        };

//        dd("ddd: from:: " . $from);

        // === Ventas al contado (pagadas) por método ===
        // Ajusta nombres de columnas según tu esquema:
        // - sale_type: 'contado' | 'credito'
        // - payment_method: 'cash' | 'transfer' | 'qr'
        // - status: 'paid'|'open' etc… (filtra solo cerradas/pagadas si aplica)
        $contado = Sale::query()
//            ->where('branch_id', $closing->branch_id)
//            ->where('user_id', $closing->user_id)
            ->where('cash_box_closing_id', $closing->id)
            ->where('status', Sale::SALE_STATUS_PAID)
            ->when(true, $range);

        $salesCash      = (clone $contado)->where('payment_method', SalePayment::METHOD_CASH)->sum('final_total');       // ajusta 'total'
        $salesTransfer  = (clone $contado)->where('payment_method', SalePayment::METHOD_TRANSFER)->sum('final_total');
        $salesQr        = (clone $contado)->where('payment_method', SalePayment::METHOD_QR)->sum('final_total');

        // === Cobros de crédito parciales por método ===
        // Ajusta al nombre real de tu tabla de pagos de crédito
        $paymentsBase = SalePayment::query()
//            ->where('branch_id', $closing->branch_id)
//            ->where('user_id', $closing->user_id)
            ->where('cash_box_closing_id', $closing->id)
            ->when(true, $range)
            ->whereHas('sale', function($q){
                $q->where('status', Sale::SALE_STATUS_CREDIT);
            });

//        dd($paymentsBase->get());
        $creditCash     = (clone $paymentsBase)->where('sale_payments.payment_method', SalePayment::METHOD_CASH)->sum('amount');
        $creditTransfer = (clone $paymentsBase)->where('sale_payments.payment_method', SalePayment::METHOD_TRANSFER)->sum('amount');
        $creditQr       = (clone $paymentsBase)->where('sale_payments.payment_method', SalePayment::METHOD_QR)->sum('amount');

        // === Movimientos manuales de caja ===
        // cash_movements: type 'income'|'expense'
        $movIncomes  = CashMovement::query()
//            ->where('branch_id', $closing->branch_id)
            ->where('cash_box_closing_id', $closing->id)
            ->where('type', CashMovement::TYPE_INCOME)
            ->when(true, $range)
            ->sum('amount');

        $movExpenses = CashMovement::query()
//            ->where('branch_id', $closing->branch_id)
            ->where('cash_box_closing_id', $closing->id)
            ->where('type', CashMovement::TYPE_EXPENSE)
            ->when(true, $range)
            ->sum('amount');

        $systemTotal =
            ($salesCash + $salesTransfer + $salesQr) +
            ($creditCash + $creditTransfer + $creditQr) +
            $closing->initial_balance +
            ($movIncomes - $movExpenses);

        return [
            'sales' => [
                'cash'     => (float) $salesCash,
                'transfer' => (float) $salesTransfer,
                'qr'       => (float) $salesQr,
            ],
            'credit_payments' => [
                'cash'     => (float) $creditCash,
                'transfer' => (float) $creditTransfer,
                'qr'       => (float) $creditQr,
            ],
            'movements' => [
                'incomes'  => (float) $movIncomes,
                'expenses' => (float) $movExpenses,
            ],
            'system_total' => (float) $systemTotal,
        ];
    }

    /**
     * Cierra la caja: fija montos y asocia registros al cierre.
     */
    public function close(CashBoxClosing $closing, float $closingAmount, ?string $notes = null, ?string $from = null, ?string $until = null, ?int $userIdFilter = null): CashBoxClosing
    {
        return DB::transaction(function () use ($closing, $closingAmount, $notes, $from, $until, $userIdFilter) {
            $totals = $this->computeTotals($closing, $from, $until, $userIdFilter);

            $closing->update([
                'closing_time'      => now(),
                'actual_balance' => $closingAmount,
                'expected_balance'   => $totals['system_total'],
                'difference'     => $closingAmount - $totals['system_total'],
                'status'         => CashBoxClosing::STATUS_CLOSED,
                'notes'          => $notes,
            ]);

            /**
            // Asociar registros al cierre (si aún no están vinculados)
            $from  = $from  ? Carbon::parse($from)  : Carbon::parse($closing->opened_at);
            $until = $until ? Carbon::parse($until) : now();

            $attach = static function (Builder $q) use ($closing, $from, $until, $userIdFilter) {
                $q->whereNull('cash_box_closing_id')
                    ->where('branch_id', $closing->branch_id)
                    ->whereBetween('created_at', [$from, $until]);

                if ($userIdFilter) {
                    $q->where('user_id', $userIdFilter);
                }

                $q->update(['cash_box_closing_id' => $closing->id]);
            };

            // Ventas contado
            Sale::query()
                ->where('sale_type', 'contado')
                ->tap($attach)
                ->getQuery(); // tap aplica sobre Builder

            // Pagos de crédito
            SalePayment::query()->tap($attach)->getQuery();

            // Movimientos
            CashMovement::query()->tap($attach)->getQuery();
            */
            return $closing->refresh();
        });
    }
}
