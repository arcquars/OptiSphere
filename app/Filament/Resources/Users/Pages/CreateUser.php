<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

//    protected function getCreateFormAction(): Action
//    {
//        return Action::make('create')
//            ->label('Crear usuario'); // Cambia el texto del botón aquí
//    }
}
