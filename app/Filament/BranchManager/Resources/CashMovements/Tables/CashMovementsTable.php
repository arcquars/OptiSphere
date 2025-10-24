<?php

namespace App\Filament\BranchManager\Resources\CashMovements\Tables;

use App\Models\CashMovement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;

class CashMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();

                if ($user && ! $user->hasRole('admin')) {
                    $query->where('user_id', $user->id);
                }
                $query->orderBy('created_at', 'desc')
                ->orderByRaw('cash_box_closing_id IS NULL, cash_box_closing_id ASC');
            })
            ->recordUrl(null)
            ->columns([
                TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->visible(fn () => auth()->user()?->hasRole('admin')),
                TextColumn::make('cash_box_closing_id')
                    ->label('Cerrado')
                    ->numeric(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        CashMovement::TYPE_INCOME => 'success', // Color verde por defecto
                        CashMovement::TYPE_EXPENSE => 'danger', // Color rojo por defecto
                    })
                    ->formatStateUsing(fn (string $state): string => __('cerisier.'.$state)),
                TextColumn::make('amount')
                    ->label('Cantidad')
                    ->numeric(),
                TextColumn::make('description')
                    ->label('Descripcion'),
                TextColumn::make('createdBy.name')
                    ->label('Creador')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn () => auth()->user()?->hasRole('admin')),
                TextColumn::make('created_at')
                    ->label('Fecha de creacion')
                    ->dateTime()
//                    ->sortable()
//                TextColumn::make('created_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo de movimiento')
                    ->options([
                        CashMovement::TYPE_INCOME => __('cerisier.'.CashMovement::TYPE_INCOME),
                        CashMovement::TYPE_EXPENSE => __('cerisier.'.CashMovement::TYPE_EXPENSE),
                    ])
                    ->placeholder('Todos los tipos'),
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->visible(fn () => auth()->user()?->hasRole('admin'))
                    ->placeholder('Todos los usuarios'),




                Filter::make('created_between')
                    ->label('Fecha (rango)')
                    ->form([
                        DatePicker::make('from')
                            ->label('Desde')
                            ->closeOnDateSelection()
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Hasta')
                            ->closeOnDateSelection()
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $from = $data['from'] ?? null;
                        $until = $data['until'] ?? null;

                        return match (true) {
                            $from && $until => "Del {$from} al {$until}",
                            $from => "Desde {$from}",
                            $until => "Hasta {$until}",
                            default => null,
                        };
                    }),




            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
