<?php

namespace App\Filament\Resources\FrequentCustomers\Pages;

use App\Filament\Resources\FrequentCustomers\FrequentCustomerResource;
use App\Services\FrequentCustomerService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditFrequentCustomer extends EditRecord
{
    protected static string $resource = FrequentCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Precarga el cliente actualmente vinculado en el selector del formulario.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['customer_id'] = $this->record->customer?->id;

        return $data;
    }

    /**
     * Delega la actualización al Service para preservar el rol y el vínculo 1 a 1.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $customerId = $data['customer_id'] ?? null;
        unset($data['customer_id']);

        return app(FrequentCustomerService::class)->update($record, $data, $customerId ? (int) $customerId : null);
    }
}
