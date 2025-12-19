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

    protected $appends = [
        'saldo_credito', 
        'document_type_show'
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

    public function getDocumentTypeShowAttribute(): string
    {
        $documentTypes = config('amyr.document_types', []);
        $dt = $documentTypes[$this->document_type] ?? 'N/A';
        $dt .= ": " . $this->nit;
        if($this->document_type == 1 && !empty($this->complement)){
            $dt .= " - Comp: " . $this->complement . "";
        }
        return "{$dt}";
    }

    /**
     * Accesor para calcular el saldo pendiente basado en la diferencia 
     * entre el total final y el monto pagado.
     * Se accede como: $customer->saldo_credito
     */
    protected function saldoCredito(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Usamos la relación definida en tu modelo
                return $this->sales()
                    ->where('status', Sale::SALE_STATUS_CREDIT)
                    ->selectRaw('SUM(final_total - paid_amount) as total_pendiente')
                    ->value('total_pendiente') ?? 0;
            },
        );
    }
}
