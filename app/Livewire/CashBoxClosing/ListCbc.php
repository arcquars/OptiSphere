<?php

namespace App\Livewire\CashBoxClosing;

use App\Models\CashBoxClosing;
use App\Models\User;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Filament\Tables\Filters\Filter; // <-- Importar la clase base de Filtros
use Filament\Forms\Components\DatePicker; // <-- Importar el componente de fecha

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
                    ->label('Sucursal')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),
                TextColumn::make('opening_time')
                    ->label('Fecha de apertura')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('closing_time')
                    ->label('Fecha de cierre')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('initial_balance')
                    ->label('Balance inicial')
                    ->numeric(),
                TextColumn::make('expected_balance')
                    ->label('Balance del Sistema')
                    ->numeric(),
                TextColumn::make('actual_balance')
                    ->label('Balance usuario')
                    ->numeric(),
                TextColumn::make('difference')
                    ->label('Diferencia')
                    ->numeric(),
                IconColumn::make('status')
                    ->label('Estado')
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
                SelectFilter::make('user_id')
                    ->label('Filtrar por Usuario')
                    ->relationship('user', 'name') // Usa la relación 'user' y muestra el campo 'name'
                    ->options(
                    // Opcional: Limitar las opciones solo a usuarios que realmente tienen cierres de caja
                        User::whereIn('id', CashBoxClosing::pluck('user_id')->unique())->pluck('name', 'id')->toArray()
                    ),
                Filter::make('closing_time')
                    ->form([
                        // Usamos dos DatePicker para definir el rango "Desde" y "Hasta"
                        DatePicker::make('date_from')
                            ->label('Cierre Desde')
                            ->placeholder(now()->subDays(30)->format('Y-m-d')),
                        DatePicker::make('date_until')
                            ->label('Cierre Hasta')
                            ->placeholder(now()->format('Y-m-d')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            // Filtro para la fecha de inicio ('date_from')
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('closing_time', '>=', $date),
                            )
                            // Filtro para la fecha de fin ('date_until')
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('closing_time', '<=', $date),
                            );
                    }),
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
