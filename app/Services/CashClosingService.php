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
        $from  = $from  ? Carbon::parse($from)  : Carbon::parse($closing->opened_at);
        $until = $until ? Carbon::parse($until) : now();

        // Base de filtros por rango/usuario
        $range = static function (Builder $q) use ($from, $until, $userIdFilter) {
            $q->whereBetween('created_at', [$from, $until]);
            if ($userIdFilter) {
                $q->where('user_id', $userIdFilter);
            }
        };

        // === Ventas al contado (pagadas) por método ===
        // Ajusta nombres de columnas según tu esquema:
        // - sale_type: 'contado' | 'credito'
        // - payment_method: 'cash' | 'transfer' | 'qr'
        // - status: 'paid'|'open' etc… (filtra solo cerradas/pagadas si aplica)
        $contado = Sale::query()
            ->where('branch_id', $closing->branch_id)
            ->where('sale_type', 'contado')
            ->when(true, $range);

        $salesCash      = (clone $contado)->where('payment_method', 'cash')->sum('total');       // ajusta 'total'
        $salesTransfer  = (clone $contado)->where('payment_method', 'transfer')->sum('total');
        $salesQr        = (clone $contado)->where('payment_method', 'qr')->sum('total');

        // === Cobros de crédito parciales por método ===
        // Ajusta al nombre real de tu tabla de pagos de crédito
        $paymentsBase = SalePayment::query()
            ->where('branch_id', $closing->branch_id)
            ->when(true, $range);

        $creditCash     = (clone $paymentsBase)->where('method', 'cash')->sum('amount');
        $creditTransfer = (clone $paymentsBase)->where('method', 'transfer')->sum('amount');
        $creditQr       = (clone $paymentsBase)->where('method', 'qr')->sum('amount');

        // === Movimientos manuales de caja ===
        // cash_movements: type 'income'|'expense'
        $movIncomes  = CashMovement::query()
            ->where('branch_id', $closing->branch_id)
            ->where('type', 'income')
            ->when(true, $range)
            ->sum('amount');

        $movExpenses = CashMovement::query()
            ->where('branch_id', $closing->branch_id)
            ->where('type', 'expense')
            ->when(true, $range)
            ->sum('amount');

        $systemTotal =
            ($salesCash + $salesTransfer + $salesQr) +
            ($creditCash + $creditTransfer + $creditQr) +
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
                'closed_at'      => now(),
                'closing_amount' => $closingAmount,
                'system_total'   => $totals['system_total'],
                'difference'     => $closingAmount - $totals['system_total'],
                'status'         => 'closed',
                'notes'          => $notes,
            ]);

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

            return $closing->refresh();
        });
    }
}
