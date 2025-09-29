<?php

namespace App\Filament\BranchManager\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;

class StoresWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stores = User::find(Auth::id())->branches;
        $stats = [];
        foreach ($stores as $i => $store){
            if($store)
                $stats[] = Stat::make($store->id, $store->name)
                ->url(fn () => route('filament.branch-manager.pages.branch-manager', ['branchId' => $store->id]));
        }
        return $stats;
    }
}
