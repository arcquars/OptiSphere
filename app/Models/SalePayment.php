<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    // Usamos estas constantes para registrar el tipo de método de pago
    const METHOD_CASH = 'EFECTIVO';
    const METHOD_CARD = 'TARJETA';
    const METHOD_QR = 'QR';
    const METHOD_TRANSFER = 'TRANSFERENCIA';
    const METHOD_OTHER = 'OTRO';

    protected $fillable = [
        'sale_id',          // Clave foránea a la venta
        'user_id',          // Usuario que registró el pago
        'branch_id',          // Sucursal
        'cash_box_closing_id',
        'deleted',
        'amount',           // Monto del abono o pago
        'payment_method',   // Método de pago (ej: EFECTIVO, TARJETA)
        'notes',            // Notas adicionales sobre el pago
    ];

    // ----------------------------------------------------
    // RELACIONES
    // ----------------------------------------------------

    /**
     * Un abono pertenece a una única venta (Sale).
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Un abono fue registrado por un único usuario (User).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
