<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    const TYPE_NORMAL = "normal";
    const TYPE_ESPECIAL = "especial";
    const TYPE_MAYORISTA = "mayorista";

    protected $fillable = [
        'name',
        'document_type',
        'complement',
        'nit',
        'address',
        'email',
        'phone',
        'contact_info',
        'can_buy_on_credit',
        'credit_limit',
        'type'
    ];

    /**
     * Un cliente tiene muchas ventas (Sales).
     * Esta es la clave para acceder a sus abonos y saldos pendientes.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // ----------------------------------------------------
    // ACCESORES (Atributos Calculados)
    // ----------------------------------------------------

    /**
     * Accessor para calcular el saldo total de crédito pendiente
     * de todas las ventas de este cliente.
     * * Se accede como: $customer->credit_balance
     */
    protected function creditBalance(): Attribute
    {
        return Attribute::make(
        // Suma el campo 'due_amount' de todas las ventas asociadas.
            get: fn (mixed $value, array $attributes) => $this->sales()->sum('due_amount'),
        );
    }
}
