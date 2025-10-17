<?php

namespace App\Filament\Resources\Services\Tables;

use App\Models\Branch;
use App\Models\Price;
use App\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


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
                    ->label('Cambiar precios')
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

                                        $priceNormal = $record->getPriceByType($state, Price::TYPE_NORMAL);
                                        $priceEspecial = $record->getPriceByType($state, Price::TYPE_ESPECIAL);
                                        $priceMayorista = $record->getPriceByType($state, Price::TYPE_MAYORISTA);

                                        // Setea los TextInput (usa null si no existe)
                                        $set('price_normal',    $priceNormal ?? null);
                                        $set('price_special',   $priceEspecial ?? null);
                                        $set('price_mayorista', $priceMayorista ?? null);
                                    }),

                                TextInput::make('price_normal')
                                    ->label('P. normal')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->step('0.01')
                                    ->required(),

                                TextInput::make('price_special')
                                    ->label('P. especial')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->step('0.01')
                                    ->required(),

                                TextInput::make('price_mayorista')
                                    ->label('P. mayorista')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->step('0.01')
                                    ->required(),
                            ])

                    ])
                    ->action(function (Service $record, array $data): void {
                        $branchId = $data['branch_id'];
                        $priceNormal = $data['price_normal'];
                        $priceSpecial = $data['price_special'];
                        $priceMayorista = $data['price_mayorista'];

                        DB::beginTransaction();

                        try {
                            $record->prices()->updateOrCreate(
                                [
                                    'type' => Price::TYPE_NORMAL,
                                    'branch_id' => $branchId,
                                ],
                                // 2. Valores para actualizar o crear
                                [
                                    'price' => $priceNormal,
                                    'user_id' => Auth::id(),
                                ]
                            );

                            $record->prices()->updateOrCreate(
                                [
                                    'type' => Price::TYPE_ESPECIAL,
                                    'branch_id' => $branchId,
                                ],
                                [
                                    'price' => $priceSpecial,
                                    'user_id' => Auth::id(),
                                ]
                            );

                            $record->prices()->updateOrCreate(
                                [
                                    'type' => Price::TYPE_MAYORISTA,
                                    'branch_id' => $branchId,
                                ],
                                [
                                    'price' => $priceMayorista,
                                    'user_id' => Auth::id(),
                                ]
                            );

                            DB::commit();
                            Notification::make()
                                ->title('Éxito')
                                ->body('Se actualizaron los precios para este producto')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            // En caso de error, deshacer todo
                            DB::rollback();

                            Notification::make()
                                ->title('Error')
                                ->body('Ocurrio un error al momento de guardar los cambios.')
                                ->success()
                                ->send();
                        }
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
