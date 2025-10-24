<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    // Tipos de movimiento: para ingresos manuales (ej: préstamo, fondo inicial extra)
    // y egresos (ej: pago a proveedor, compra de insumos menores).
    const TYPE_INCOME = 'INCOME';
    const TYPE_EXPENSE = 'EXPENSE';

    protected $fillable = [
        'branch_id', // La caja pertenece a una sucursal.
        'user_id',   // Usuario que registró el movimiento.
        'type',      // INCOME o EXPENSE.
        'amount',    // Monto del movimiento.
        'description', // Razón del movimiento (ej: "Pago a proveedor X", "Fondo extra").
        'cash_box_closing_id', // Para ligarlo a un cierre (opcional al inicio).
        'created_by'
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class); // Asumo que tienes un modelo Branch
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // Asumo que tienes un modelo User
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::creating(function ($cashMovement) {
            if (! $cashMovement->created_by && auth()->check()) {
                $cashMovement->created_by = auth()->id();
            }
        });
    }
}
