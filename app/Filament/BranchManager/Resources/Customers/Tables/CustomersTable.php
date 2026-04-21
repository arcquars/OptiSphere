<?php

namespace App\Filament\BranchManager\Resources\Customers\Tables;

use App\Filament\BranchManager\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table, $branchId): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query->where('branch_id', $branchId)
            )
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->label('Nombres')
                    ->searchable(),
                TextColumn::make('nit')
                    ->label('NIT')
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Dirección')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                TextColumn::make('contact_info')
                    ->label('Información de contacto')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->searchable(),
                IconColumn::make('can_buy_on_credit')
                    ->label('Crédito')
                    ->boolean(),
                TextColumn::make('type')->label('Tipo'),
                
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()
                    // Construir la URL de edición con branch_id
                    ->url(fn (Customer $record): string =>
                        CustomerResource::getUrl('edit', [
                            'record'    => $record,
                        ])
                    ),
                // EditAction::make()
                //     ->label('')
                //     ->tooltip('Editar')
                //     ->icon('fas-pen-to-square')
                //     ->url(fn ($record): string => CustomerResource::getUrl('edit', [
                //         'record' => $record,
                //         'branch_id' => request()->route('branch_id') ?? request()->query('branch_id'),
                //     ]))
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $branchId = request()->route('branch_id');

        return static::getEloquentQuery()
            ->when($branchId, fn (Builder $query) =>
                $query->where('branch_id', $branchId)
            );
    }
}
