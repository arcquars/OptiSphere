<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Filament\Exports\SalePaymentExporter;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\View\View;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Actions\ExportAction;

class CreditPaymentResource extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.credit-payment-resource';

    protected static string|\BackedEnum|null $navigationIcon = "c-banknotes";

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pagos a cuenta de Ventas a crédito';

    public static function getNavigationLabel(): string
    {
        return __('Pagos a cuenta de Ventas');
    }

    /**
     * Configuración de la tabla.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                SalePayment::query()
                    ->where('deleted', false)
                    ->whereHas('sale', function ($query){
                        $query->where('sales.status', Sale::SALE_STATUS_CREDIT);
                    })
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                // Botón de exportación a Excel
                ExportAction::make()
                    ->label('Exportar Excel')
                    ->exporter(SalePaymentExporter::class)
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->columnMapping(false)
            ])
            ->columns([
                TextColumn::make('sale.date_sale')
                    ->label('Fecha de venta')
                    ->date('Y-m-d')
                    // ->searchable()
                    ->sortable(),
                TextColumn::make('sale.customer.name')
                    ->label('Cliente')
                    // ->searchable()
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
                    ->label('Método de pago'),
                TextColumn::make('branch.name')
                    ->label('Sucursal'),
                TextColumn::make('residue')
                    ->label('Saldo')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),
                Tables\Columns\IconColumn::make('sale.is_paid')
                    ->label('Pagado')
                    ->boolean()
                    ->alignCenter()
            ])
            ->filters([
                // Filtro por estado de pago
                Tables\Filters\TernaryFilter::make('sale')
                    ->label('Estado de Pago')
                    ->trueLabel('Pagado')
                    ->falseLabel('Con saldo')
                    ->queries(
                        true: fn ($query) => $query->whereHas('sale', function ($query){
                            $query->whereRaw('sales.final_total = sales.paid_amount');
                        }),
                        false: fn ($query) => $query->whereHas('sale', function ($query){
                            $query->whereRaw('sales.final_total <> sales.paid_amount');
                        }),
                    ),
                
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('customer')
                    ->label('Cliente')
                    ->relationship('sale.customer', 'name')
                    ->searchable()
                    ->preload(),

                // Filtro de Rango de Fechas con Indicadores
                Filter::make('created_at')
                    ->label('Rango de fechas')
                    ->schema([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Desde: ' . Carbon::parse($data['from'])->format('d/m/Y'))
                                ->removeField('from');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Hasta: ' . Carbon::parse($data['until'])->format('d/m/Y'))
                                ->removeField('until');
                        }
                        return $indicators;
                    })
            ])
            ->actions([
                Action::make('view_history')
                    ->label('')
                    ->icon('far-clock')
                    ->modalHeading('Historial y Registro de Abonos')
                    ->modalContent(fn (SalePayment $record): View => view(
                        'filament.pages.actions.sale-payment-history',
                        ['salePayment' => $record],
                    ))
                    // Definición del esquema condicional
                    ->schema(fn (SalePayment $record): array => $record->sale->is_paid 
                        ? [] 
                        : [
                            Grid::make(2)->schema([
                                TextInput::make('amount')
                                    ->label('Monto del abono')
                                    ->numeric()
                                    ->step('0.01')
                                    ->required()
                                    ->prefix(config('cerisier.currency_symbol', '$'))
                                    ->maxValue(fn (SalePayment $record) => number_format($record->sale->final_total - $record->sale->paid_amount, 2))
                                    ->hint(fn (SalePayment $record) => 'Saldo pendiente: ' . number_format($record->sale->final_total - $record->sale->paid_amount, 2)),
                                
                                Select::make('payment_method')
                                    ->label('Método de Pago')
                                    ->options([
                                        'EFECTIVO' => 'Efectivo',
                                        'TRANSFERENCIA' => 'Transferencia'
                                    ])
                                    ->default('EFECTIVO')
                                    ->required(),
                            ])
                        ]
                    )
                    ->action(function (array $data, SalePayment $record): void {
                        DB::transaction(function () use ($data, $record) {
                            $sale = $record->sale;
                            $currentResidue = $sale->final_total - $sale->paid_amount;
                            
                            SalePayment::create([
                                'sale_id' => $sale->id,
                                'user_id' => auth()->id(),
                                'branch_id' => $record->branch_id,
                                'amount' => $data['amount'],
                                'payment_method' => $data['payment_method'],
                                'residue' => $currentResidue - $data['amount'],
                                'deleted' => false,
                            ]);

                            $sale->increment('paid_amount', $data['amount']);
                        });

                        Notification::make()
                            ->title('Pago registrado correctamente')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitAction(fn (SalePayment $record) => $record->sale->is_paid ? false : null)
                    ->modalSubmitActionLabel('Registrar pago')
                    ->modalCancelActionLabel('Cerrar'),
            ])
            ->paginated();
    }
}