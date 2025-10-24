<?php

namespace App\Filament\BranchManager\Resources\CashMovements\Pages;

use App\Filament\BranchManager\Resources\CashMovements\CashMovementResource;
use App\Models\CashBoxClosing;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateCashMovement extends CreateRecord
{
    protected static string $resource = CashMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        // Verificar si existe caja abierta para la sucursal seleccionada
        $exists = CashBoxClosing::where('branch_id', $data['branch_id'] ?? null)
            ->where('user_id', $user->id)
            ->where('status', CashBoxClosing::STATUS_OPEN)
            ->exists();

        if (! $exists) {
            Notification::make()
                ->title('No existe una caja abierta para esta sucursal.')
                ->danger() // O ->danger(), ->warning(), ->info()
                ->send();
            throw ValidationException::withMessages([
                'branch_id' => 'No existe una caja abierta para esta sucursal. Debes abrir una antes de registrar movimientos.',
            ]);

        }
        $data['cash_box_closing_id'] = CashBoxClosing::where('branch_id', $data['branch_id'] ?? null)
            ->where('user_id', $user->id)
            ->where('status', CashBoxClosing::STATUS_OPEN)
            ->first()->id;
        return $data;
    }
}
