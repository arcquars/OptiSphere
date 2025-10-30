<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\OpticalProperty;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if(!$this->data['has_optical_properties']){
            /** @var OpticalProperty $op */
            $op = OpticalProperty::where('product_id', $this->record->id)->first();
            $op->delete();
        }
    }
}
