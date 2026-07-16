<?php

namespace App\Filament\Resources\FrequentCustomers\Pages;

use App\Filament\Resources\FrequentCustomers\FrequentCustomerResource;
use App\Services\FrequentCustomerService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFrequentCustomer extends CreateRecord
{
    protected static string $resource = FrequentCustomerResource::class;

    /**
     * Delega la creación al Service: crea el usuario, le asigna el rol
     * y lo vincula al registro de customers seleccionado.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $customerId = $data['customer_id'] ?? null;
        unset($data['customer_id']);

        return app(FrequentCustomerService::class)->create($data, $customerId ? (int) $customerId : null);
    }
}
