<?php

namespace App\Filament\Resources\Promotions\Pages;
use App\Filament\Resources\Promotions;
use App\Models\Promotion;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;

class AttachPromotionables extends Page
{

    protected static string $resource = Promotions\PromotionResource::class;

    protected string $view = 'filament.resources.promotions.pages.attach-promotionables';

    public Promotion $record;

    // Título que se mostrará en la navegación de la miga de pan
    protected static ?string $title = 'Vincular Artículos';

    // Icono opcional
//    protected static ?string $navigationIcon = 'heroicon-o-link';


}
