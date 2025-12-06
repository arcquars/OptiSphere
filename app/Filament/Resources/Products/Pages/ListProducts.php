<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\OpticalProperty;
use App\Models\Product;
use App\Services\DeleteByBaseCodeService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deleteByBaseCode')
                ->label('Eliminar por código base')
                ->schema([
                    Select::make('baseCode')
                        ->label('Código Base')
                        ->options(OpticalProperty::groupBy('base_code')->pluck('base_code', 'base_code'))
                        // OpticalProperty::groupBy('base_code')->pluck('base_code', 'base_code')
                        ->required(),
                ])
                ->color('danger')
                ->modalSubmitActionLabel('Eliminar')
                ->action(function (DeleteByBaseCodeService $dbbcs, array $data): void {
                    try{
                        $dbbcs->delete($data['baseCode']);
                        Notification::make()
                            ->title('Se eliminaron correctamente los productos con Código base ' . $data['baseCode'].'.')
                            ->info()
                            ->send();
                    } catch (\Exception $e){
                        Notification::make()
                            ->title('Ocurrio el siguiente error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }

                }),
            Action::make('create-base-code')
                ->label('Crear con códigos Base')
                ->url(fn (): string => route('filament.admin.resources.products.generate'))
                ->color('success'),
            Action::make('edit-base-code')
                ->label('Editar códigos Base')
                ->url(fn (): string => route('filament.admin.resources.products.generate-edit'))
                ->color('success'),
            CreateAction::make()
        ];
    }
}
