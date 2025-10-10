<?php

namespace App\Filament\Resources\Services\Tables;

use App\Models\Branch;
use App\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;


class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre del Servicio')
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Código / SKU')
                    ->searchable(),
                ImageColumn::make('path_image')
                    ->label('Imagen'),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('categories.name')
                    ->label('Categorías')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->categories->pluck('name')->join(', ');
                    })
                    ->badge() // opcional: muestra como badges
                    ->sortable(false)
                    ->searchable(false)
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('set-price-service')
//                    ->label('Precios para ')
                    ->modalHeading(fn (Service $record) => "Precios para: {$record->name}")
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 3
                        ])
                            ->schema([
                                Select::make('branch_id')
                                    ->label('Sucursal')
                                    ->options(fn () => Branch::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(3)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get, Service $record) {
                                        // $state es el branch_id seleccionado
                                        if (! $state) {
                                            // Limpia campos si no hay sucursal
                                            $set('price_normal', null);
                                            $set('price_special', null);
                                            $set('price_mayorista', null);
                                            return;
                                        }

                                        // === Trae precios según tu modelo/diseño de datos ===
                                        // 1) Si tienes una relación en Service: branchPrices()->where('branch_id', X)
                                        //    con columnas: normal, special, mayorista
                                        $price = $record->branchPrices()
                                            ->where('branch_id', $state)
                                            ->first();

                                        // 2) O si tienes una tabla service_branch_price, haz tu propia consulta:
                                        // $price = \App\Models\ServiceBranchPrice::where('service_id', $record->id)
                                        //     ->where('branch_id', $state)
                                        //     ->first();

                                        // 3) O si guardas precios en JSON por sucursal en el Service, lee desde ahí.

                                        // Setea los TextInput (usa null si no existe)
                                        $set('price_normal',    optional($price)->normal ?? null);
                                        $set('price_special',   optional($price)->special ?? null);
                                        $set('price_mayorista', optional($price)->mayorista ?? null);
                                    }),

                                TextInput::make('price_normal')
                                    ->label('P. normal')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->step('0.01'),

                                TextInput::make('price_special')
                                    ->label('P. especial')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->step('0.01'),

                                TextInput::make('price_mayorista')
                                    ->label('P. mayorista')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->step('0.01'),
                            ])

                    ])
                    ->action(function (Service $record): void {
                        dd();
                    })
                    ->modalWidth(Width::Small)
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
