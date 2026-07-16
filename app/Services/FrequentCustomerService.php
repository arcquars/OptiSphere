<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class FrequentCustomerService
{
    // Rol de Spatie que identifica a un usuario cliente frecuente
    public const ROLE = 'frequent-customer';

    /**
     * Crea un usuario cliente frecuente, le asigna el rol y lo vincula
     * a un registro existente de customers.
     *
     * @throws \Throwable si la transacción falla
     */
    public function create(array $data, ?int $customerId): User
    {
        return DB::transaction(function () use ($data, $customerId): User {
            $user = User::create($data);
            $user->assignRole(self::ROLE);

            $this->linkCustomer($user, $customerId);

            return $user;
        });
    }

    /**
     * Actualiza los datos del usuario cliente frecuente y su vínculo con customers.
     *
     * @throws \Throwable si la transacción falla
     */
    public function update(User $user, array $data, ?int $customerId): User
    {
        return DB::transaction(function () use ($user, $data, $customerId): User {
            $user->update($data);

            // Garantiza que conserve el rol aunque se haya editado
            if (! $user->hasRole(self::ROLE)) {
                $user->assignRole(self::ROLE);
            }

            $this->linkCustomer($user, $customerId);

            return $user->refresh();
        });
    }

    /**
     * Vincula (o revincula) un registro de customers a este usuario, liberando
     * cualquier vínculo previo para preservar la relación uno a uno.
     */
    private function linkCustomer(User $user, ?int $customerId): void
    {
        // Libera el cliente actualmente vinculado si es distinto al nuevo
        Customer::query()
            ->where('user_id', $user->id)
            ->when($customerId, fn ($query) => $query->whereKeyNot($customerId))
            ->update(['user_id' => null]);

        if ($customerId !== null) {
            Customer::whereKey($customerId)->update(['user_id' => $user->id]);
        }
    }
}
