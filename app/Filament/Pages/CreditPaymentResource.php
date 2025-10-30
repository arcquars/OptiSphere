<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SalePayment;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CreditPaymentResource extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.credit-payment-resource';

    protected static string|\BackedEnum|null $navigationIcon = "c-banknotes";

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pagos a cuenta de Ventas a crédito';

    // (OPCIONAL) El texto que quieres en el menú.
    // Si no lo pones, usará el título (ej. "Mi Pagina Simple")
    public static function getNavigationLabel(): string
    {
        return __('Pagos a cuenta de Ventas');
    }

    /**
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                SalePayment::query()->where('deleted', false)->whereHas('sale', function ($query){
                    $query->where('sales.status', Sale::SALE_STATUS_CREDIT);
                })
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('sale.date_sale')
                    ->label('Fecha de venta')
                    ->date('Y-m-d')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sale.customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sale.final_total')
                    ->label('Monto de venta')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),
                TextColumn::make('created_at')
                    ->label('Fecha de pago')
                    ->date('Y-m-d'),
                TextColumn::make('user.name')
                    ->label('Usuario'),
                TextColumn::make('amount')
                    ->label('Cantidad')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),
                TextColumn::make('payment_method')
                    ->label('Metodo de pago'),
                TextColumn::make('branch.name')
                    ->label('Sucursal'),
                TextColumn::make('balance')
                    ->label('Saldo')
                    ->state(function (SalePayment $record): float {
                        // Asumiendo que $record tiene una relación 'sale'
                        if (!$record->sale) {
                            return 0;
                        }
                        return $record->sale->final_total - $record->sale->paid_amount;
                    })
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),
                Tables\Columns\IconColumn::make('sale.is_paid')
                    ->label('Pagado')
                    ->boolean()
                    ->alignCenter()
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('sale')
                    ->label('Pagado / Con saldo') // El título del filtro
                    ->trueLabel('Pagado')   // Etiqueta para 'Verdadero'
                    ->falseLabel('Con saldo') // Etiqueta para 'Falso'
                    ->queries(
                    // Lógica cuando se selecciona 'Verdadero'
                        true: fn ($query) => $query->whereHas('sale', function ($query){
                            $query->whereRaw('sales.final_total = sales.paid_amount');
                        }),
                        // Lógica cuando se selecciona 'Falso'
                        false: fn ($query) => $query->whereHas('sale', function ($query){
                            $query->whereRaw('sales.final_total <> sales.paid_amount');
                        }),
                    // 'null' (opcional) es cuando se selecciona 'Todos'
                    ),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('customer')
                    ->label('Cliente')
                    ->relationship('sale.customer', 'name')
                    ->searchable()
                    ->preload()
            ])
            ->actions([
                // Aquí puedes añadir acciones por fila
            ])
            ->bulkActions([
                // Aquí puedes añadir acciones masivas
            ])
            ->paginated(); // Habilitar paginación
    }
}
