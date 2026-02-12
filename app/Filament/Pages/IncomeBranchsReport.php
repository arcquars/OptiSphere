<?php

namespace App\Filament\Pages;

use App\Filament\Exports\CashBoxClosingExporter;
use App\Models\CashBoxClosing;
use App\Services\CashClosingService;
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
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;

class IncomeBranchsReport extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.pages.income-branchs-report';

    protected static string|\BackedEnum|null $navigationIcon = "c-banknotes";

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Reporte de Ingresos - Cajas';

    public static function getNavigationLabel(): string
    {
        return __('Reporte de Ingresos Cajas');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CashBoxClosing::query()
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                // Botón de exportación a Excel
                ExportAction::make()
                    ->label('Exportar Excel')
                    ->exporter(CashBoxClosingExporter::class)
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->columnMapping(false)
            ])
            ->columns([
                TextColumn::make('branch.name')
                    ->label('Sucursal'),
                TextColumn::make('user.name')
                    ->label('Usuario'),
                TextColumn::make('opening_time')
                    ->label('Fecha de Apertura')
                    ->date('Y-m-d h:m')
                    // ->searchable()
                    ->sortable(),
                TextColumn::make('closing_time')
                    ->label('Fecha de Cierre')
                    ->date('Y-m-d h:m')
                    // ->searchable()
                    ->sortable(),
                TextColumn::make('initial_balance')
                    ->label('Balance inicial')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),
                TextColumn::make('expected_balance')
                    ->label('Balance sistema')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),
                TextColumn::make('actual_balance')
                    ->label('Balance actual')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),
                TextColumn::make('difference')
                    ->label('Diferencia')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),    
                IconColumn::make('status')
                    ->color(fn (string $state): string => match ($state) {
                        'closed' => 'info',
                        'open' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'closed' => Heroicon::LockClosed,
                        'open' => Heroicon::LockOpen,
                        // 'published' => Heroicon::OutlinedCheckCircle,
                    })
                    ->alignCenter()
            ])
            ->recordActions([
                Action::make('delete')
                    ->hiddenLabel(true)
                    ->icon('fas-eye')
                    ->color("info")
                    ->schema(fn (CashBoxClosing $record): array => strcmp($record->status, "closed") == 0
                        ? [] 
                        : [
                            Grid::make(3)->schema([
                                TextInput::make('closingAmount')
                                    ->label('Efectivo contado por cajero')
                                    ->numeric()
                                    ->step('0.01')
                                    ->required()
                                    ->prefix(config('cerisier.currency_symbol', '$')),
                                    // ->maxValue(fn (CashBoxClosing $record) => number_format($record->sale->final_total - $record->sale->paid_amount, 2)),
                                Textarea::make('notes')
                                ->label('Notas')
                                ->columnSpan(2)
                            
                            ])
                        ]
                    )
                    ->action(function (array $data, CashBoxClosing $record) {
                        $svc = app(CashClosingService::class);
                        $closing = $svc->close(
                            closing: $record,
                            closingAmount: (float) $data['closingAmount'],
                            notes: $data['notes'],
                            from: null,
                            until: null,
                            userIdFilter: null,
                        );

                        if($closing){
                            Notification::make()
                                ->title('Cerrar Caja')
                                ->body("Caja cerrada correctamente.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                            ->title('Cerrar Caja')
                            ->body("Ocurrió un error al momento de cerrar la caja, contáctese con el Administrador.")
                            ->danger()
                            ->send();
                        }
                        
                    })
                    ->modalContent(function (CashBoxClosing $record): View {
                        $svc = app(CashClosingService::class);
                        $totals = $svc->computeTotals(
                            closing: $record,
                            from: null,
                            until: null,
                            userIdFilter: null,
                        );
                        return view(
                            'filament.pages.actions.cash-box-clocing',
                            ['record' => $record, 'totals' => $totals],
                        );
                    })
                    ->modalHeading("Ingreso de Sucursal")
                    ->modalSubmitAction(fn (CashBoxClosing $record) => (strcmp($record->status, 'open') != 0) ? false : null)
                    ->modalSubmitActionLabel('Cerrar caja')
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'open' => 'Abierto',
                        'closed' => 'Cerrado'
                    ]),
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('opening_time')
                ->label('Fecha de apertura')
                ->schema([
                    DatePicker::make('opening_time')
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['opening_time'],
                            fn (Builder $query, $date): Builder => $query->whereDate('opening_time', '>=', $date),
                        );
                })
            ])
            ->paginated();
    }
}
