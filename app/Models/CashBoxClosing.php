<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashBoxClosing extends Model
{
    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'branch_id',
        'user_id',           // Usuario que realiza el cierre.
        'opening_time',      // Hora y fecha de apertura de la caja.
        'closing_time',      // Hora y fecha del cierre.
        'initial_balance',   // Saldo inicial de la caja.
        'expected_balance',  // Saldo calculado por el sistema (ventas + cobros + ingresos - egresos).
        'actual_balance',    // Saldo físico contado por el usuario.
        'difference',        // expected_balance - actual_balance.
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_time' => 'datetime',
        'closing_time' => 'datetime',
        'initial_balance' => 'float',
        'expected_balance' => 'float',
        'actual_balance' => 'float',
        'difference' => 'float',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación con todos los movimientos de ingresos/egresos manuales registrados en este cierre
    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class, 'cash_box_closing_id');
    }

    // Relación con las ventas realizadas durante este período de cierre
    // (Asume que Sale tiene un campo 'cash_box_closing_id' o filtra por fecha/hora)
    public function sales(): HasMany
    {
        // Usaremos el filtro por fecha/hora en la práctica, pero si la relacionas,
        // podrías agregar una columna cash_box_closing_id a la tabla sales/sale_payments.
        return $this->hasMany(Sale::class);
    }

    public static function isOpenCashBoxByBranchAndUser($branchId, $userId){
        $user = User::find($userId);
        if($user->hasRole('admin')){
            return true;
        }
        /** @var Branch $branch */
        $branch = Branch::find($branchId);

        return $branch->isOpenCashBoxClosingByUser($userId);
    }
}
