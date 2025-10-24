<?php

namespace App\Filament\BranchManager\Resources\CashMovements\Pages;

use App\Filament\BranchManager\Resources\CashMovements\CashMovementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCashMovement extends ViewRecord
{
    protected static string $resource = CashMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
