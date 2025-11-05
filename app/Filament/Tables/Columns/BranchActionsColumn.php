<?php

namespace App\Filament\Tables\Columns;

use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Tables\Columns\Column;
use Filament\Forms\Components\Select;

class BranchActionsColumn extends Column
{
    protected string $view = 'filament.tables.columns.branch-actions-column';
}
