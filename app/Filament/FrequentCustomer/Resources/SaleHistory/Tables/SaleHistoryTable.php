<?php

namespace App\Filament\FrequentCustomer\Resources\SaleHistory\Tables;

use App\Models\Sale;
use App\Services\ProductAuthenticationService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SaleHistoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Venta')
                    ->numeric(),
                TextColumn::make('date_sale')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Sucursal'),
                TextColumn::make('final_total')
                    ->label('Total')
                    ->money('BOB'),
                TextColumn::make('paid_amount')
                    ->label('Pagado')
                    ->money('BOB'),
                TextColumn::make('due_amount')
                    ->label('Saldo')
                    ->money('BOB'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Sale::SALE_STATUS_PAID => 'Pagado',
                        Sale::SALE_STATUS_PARTIAL_PAYMENT => 'Pago parcial',
                        Sale::SALE_STATUS_CREDIT => 'Crédito',
                        Sale::SALE_STATUS_VOIDED => 'Anulado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Sale::SALE_STATUS_PAID => 'success',
                        Sale::SALE_STATUS_PARTIAL_PAYMENT => 'warning',
                        Sale::SALE_STATUS_CREDIT => 'info',
                        Sale::SALE_STATUS_VOIDED => 'danger',
                        default => 'gray',
                    }),
            ])
            ->headerActions([
                // Un único botón en la cabecera de la tabla (no por fila)
                Action::make('autentificar_producto')
                    ->label('Autentificar Producto')
                    ->icon('heroicon-o-shield-check')
                    ->button()
                    ->modalHeading('Autentificar Producto')
                    // Solo disponible si el usuario tiene un cliente vinculado
                    ->visible(fn (): bool => Auth::user()?->customer !== null)
                    ->schema([
                        Select::make('product_id')
                            ->label('Producto')
                            ->searchable()
                            ->required()
                            ->live()
                            // Solo los productos comprados por el cliente, con su cantidad acumulada
                            ->options(fn (): array => app(ProductAuthenticationService::class)
                                ->purchasedProductOptions((int) Auth::user()->customer->id)),
                        Grid::make(2)
                            ->dense()
                            ->visible(fn (Get $get): bool => filled($get('product_id')))
                            ->schema([
                                TextInput::make('cliente')
                                    ->label('Datos del cliente que compró')
                                    ->required(),
                                DatePicker::make('fecha_compra')
                                    ->label('Fecha de compra')
                                    ->required()
                                    ->default(fn (): ?string => app(ProductAuthenticationService::class)
                                        ->lastPurchaseDate((int) Auth::user()->customer->id)),
                            ]),
                        // Receta óptica: opcional, no todos los productos la llevan.
                        // Se agrupa en una Section colapsable para no alargar el modal cuando
                        // el producto autentificado no requiere fórmula (armazón, accesorio).
                        Section::make('Receta óptica')
                            ->description('Opcional: solo si el producto autentificado es un lente con fórmula')
                            ->icon('heroicon-o-eye')
                            ->iconColor('primary')
                            ->collapsible()
                            ->collapsed()
                            ->compact()
                            ->dense()
                            ->visible(fn (Get $get): bool => filled($get('product_id')))
                            ->schema([
                                // Un Fieldset por ojo: la etiqueta del campo ya no repite "OD"/"OI",
                                // el layout mismo (fila = ojo, columna = Esfera/Cilindro/Eje) reproduce
                                // la tabla clínica de la receta.
                                Grid::make(2)
                                    ->dense()
                                    ->schema([
                                        Fieldset::make('OD (ojo derecho)')
                                            ->columns(3)
                                            ->dense()
                                            ->schema([
                                                TextInput::make('od_sphere')
                                                    ->label('Esfera')
                                                    ->numeric()
                                                    ->suffix('D'),
                                                TextInput::make('od_cylinder')
                                                    ->label('Cilindro')
                                                    ->numeric()
                                                    ->suffix('D'),
                                                TextInput::make('od_axis')
                                                    ->label('Eje')
                                                    ->numeric()
                                                    ->suffix('°'),
                                            ]),
                                        Fieldset::make('OI (ojo izquierdo)')
                                            ->columns(3)
                                            ->dense()
                                            ->schema([
                                                TextInput::make('oi_sphere')
                                                    ->label('Esfera')
                                                    ->numeric()
                                                    ->suffix('D'),
                                                TextInput::make('oi_cylinder')
                                                    ->label('Cilindro')
                                                    ->numeric()
                                                    ->suffix('D'),
                                                TextInput::make('oi_axis')
                                                    ->label('Eje')
                                                    ->numeric()
                                                    ->suffix('°'),
                                            ]),
                                    ]),
                                Grid::make(2)
                                    ->dense()
                                    ->schema([
                                        TextInput::make('add')
                                            ->label('ADD')
                                            ->numeric()
                                            ->suffix('D'),
                                        TextInput::make('dip')
                                            ->label('DIP')
                                            ->numeric()
                                            ->suffix('mm'),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data): void {
                        // La validación del tope vive en el Service (ValidationException con
                        // clave 'product_id'). Esa clave no coincide con el statePath real del
                        // campo dentro de un mounted action (mountedActions.{i}.data.product_id),
                        // así que Filament no la muestra bajo el campo: se notifica explícitamente
                        // (mismo patrón ya usado en CreateCashMovement.php) y se relanza para
                        // mantener el comportamiento de "halt" de la acción.
                        try {
                            app(ProductAuthenticationService::class)
                                ->authenticate((int) Auth::user()->customer->id, $data);
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title($exception->validator->errors()->first('product_id'))
                                ->danger()
                                ->send();

                            throw $exception;
                        }

                        Notification::make()
                            ->title('Producto autenticado')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
