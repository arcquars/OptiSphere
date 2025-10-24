<?php

namespace App\Filament\BranchManager\Pages;

use Filament\Pages\Page;
use \App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class CashClosing extends Page
{
    protected string $view = 'filament.branch-manager.pages.cash-closing';

    protected static ?string $title = 'Cierre de caja';

    protected static bool $shouldRegisterNavigation = true;

}
