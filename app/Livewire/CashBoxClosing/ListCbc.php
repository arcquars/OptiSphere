<?php

namespace App\Livewire\CashBoxClosing;

use App\Models\CashBoxClosing;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListCbc extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public $branchId;

    public function mount($branchId){
        $this->branchId = $branchId;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => CashBoxClosing::where('branch_id', '=',$this->branchId))
            ->recordUrl(
                fn (CashBoxClosing $record): string => route('filament.admin.resources.branches.cash-box-view', ['cashBoxClosingId' => $record->id]),
            )
            ->columns([
                TextColumn::make('branch.name')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('opening_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('closing_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('initial_balance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expected_balance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('actual_balance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('difference')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('status')
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        CashBoxClosing::STATUS_OPEN => Heroicon::OutlinedLockOpen,
                        CashBoxClosing::STATUS_CLOSED => Heroicon::OutlinedLockClosed,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        CashBoxClosing::STATUS_OPEN => 'info',
                        CashBoxClosing::STATUS_CLOSED => 'warning',
                        default => 'gray',
                    }),
//                TextColumn::make('status')
//                    ->searchable(),
            ])
            ->defaultSort('initial_balance', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.cash-box-closing.list-cbc');
    }
}
