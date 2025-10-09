<?php

namespace App\Filament\BranchManager\Pages;

use Filament\Pages\Page;

class SalesReport extends Page
{
    protected string $view = 'filament.branch-manager.pages.sales-report';

    protected static ?string $title = 'Reporte de ventas';

    protected static bool $shouldRegisterNavigation = true;
}
