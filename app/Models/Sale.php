<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Sale extends Model
{
    use HasFactory;

    const SALE_STATUS_PAID = 'PAID';
    const SALE_STATUS_PARTIAL_PAYMENT = 'PARTIAL PAYMENT';
    const SALE_STATUS_CREDIT = 'CREDIT';
    const SALE_STATUS_VOIDED = 'VOIDED';

    protected $fillable = [
        'branch_id',
        'customer_id',
        'user_id',
        'total_amount',
        'final_discount',
        'final_total',
        'status',
        'payment_method',
        'sale_type',
        'paid_amount', // Monto almacenado
        'due_amount',  // Saldo almacenado (aunque se puede recalcular con el accessor)
        'notes',
        'date_sale'
    ];

    protected $casts = [
        'total_amount' => 'float',
        'final_discount' => 'float',
        'final_total' => 'float',
        'date_sale' => 'date',
        'voided_at' => 'datetime'

    ];

    /**
     * Relación con el detalle de la venta.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Relación con la sucursal.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con el cliente.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación con el usuario que realizó la venta.
     */
    public function user(): BelongsTo
    {
        // Asumiendo que tienes un modelo User
        return $this->belongsTo(User::class);
    }

    /**
     * Relación uno a muchos con los abonos/pagos de la venta.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function scopeNotVoided($q) {
        return $q->where('status', '!=', 'voided');
    }

    public function isVoided(): bool {
        return $this->status === 'voided';
    }

    // ... otras relaciones (customer, branch, user)

    // ---------------------------------------------
    // 2. ACCESSOR (Mutator) para el saldo pendiente
    // ---------------------------------------------

    /**
     * Calcula automáticamente el saldo pendiente (due_amount).
     * * NOTA: Este accessor se basa en el campo 'paid_amount' que es
     * actualizado por el CreditService cuando se realiza un abono,
     * no en la suma en tiempo real de la tabla 'sale_payments'.
     * Esto mejora el rendimiento.
     * * @return Attribute
     */
    protected function dueAmount(): Attribute
    {
        return Attribute::make(
        // El 'get' se dispara cuando accedes a $sale->due_amount
            get: fn (float $value, array $attributes) => $attributes['final_total'] - $attributes['paid_amount'],
        )->shouldCache(); // Recomendado para atributos calculados.
    }

    // ---------------------------------------------
    // (OPCIONAL) 3. Accessor para verificar el monto pagado
    // ---------------------------------------------

    /**
     * Opcionalmente, puedes calcular el total pagado sumando la relación.
     * Esto NO debe usarse para mostrar el saldo, sino para auditoría,
     * ya que es más lento que usar la columna almacenada 'paid_amount'.
     */
    protected function totalPaidByPayments(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->payments()->sum('amount'),
        );
    }

    protected function usePromotion(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items()->whereNotNull('promotion_id')->exists(),
        )->shouldCache();
    }

    protected function amountProducts(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items()->where('salable_type', Product::class)->count(),
        )->shouldCache();
    }

    protected function amountServices(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                // Puedes agregar código, lógica o depuración aquí
                // Ejemplo: \Log::info('Calculando cantidad de servicios para Venta ID: ' . $this->id);

                $serviceCount = $this->items()
                    ->where('salable_type', Service::class)
                    ->count();

                $subServices = 0;
                foreach ($this->items as $i){
                    $subServices += count($i->attachedServices);
                }

                // Retorno explícito
                return $serviceCount + $subServices;
            },
        )->shouldCache();
    }
}
