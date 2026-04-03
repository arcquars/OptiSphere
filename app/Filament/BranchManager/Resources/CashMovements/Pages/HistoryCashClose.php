<?php

namespace App\Filament\BranchManager\Resources\CashMovements\Pages;

use App\Filament\BranchManager\Resources\CashMovements\CashMovementResource;
use App\Models\CashBoxClosing;
use App\Services\CashClosingService;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class HistoryCashClose extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = CashMovementResource::class;

    protected string $view = 'filament.branch-manager.resources.cash-movements.pages.history-cash-close';

    protected static ?string $title = 'Historial Cierre de caja';

    public $branch;
    public $userId;

    public function mount(int $branchId, int $userId): void
    {
        // Aquí puedes cargar la sucursal o hacer validaciones
        $this->branch = \App\Models\Branch::find($branchId);
        $this->userId = $userId;
    }

    /**
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                CashBoxClosing::query()
                ->where('branch_id', $this->branch->id)
                ->where('user_id', $this->userId)
                ->whereHas('branch')
            )
            ->extraAttributes([
                'class' => 'tabla-cerisier shadow-md border-separate border-spacing-0',
            ])
            ->defaultSort('opening_time', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('opening_time')
                    ->label('F. Apertura')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('closing_time')
                    ->label('F. Cierre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('initial_balance')
                    ->label('B. inicial')
                    ->money(config('cerisier.currency_symbol')) // Asumo Bolivianos, puedes cambiarlo o quitarlo
                    ->alignEnd()
                    ->default(0),
                Tables\Columns\TextColumn::make('expected_balance')
                    ->label('B. Sistema')
                    ->money(config('cerisier.currency_symbol')) // Asumo Bolivianos, puedes cambiarlo o quitarlo
                    ->alignEnd()
                    ->default(0),
                Tables\Columns\TextColumn::make('actual_balance')
                    ->label('B. Actual')
                    ->money(config('cerisier.currency_symbol')) // Asumo Bolivianos, puedes cambiarlo o quitarlo
                    ->alignEnd()
                    ->default(0),
                Tables\Columns\TextColumn::make('difference')
                    ->label('Diferencia')
                    ->money(config('cerisier.currency_symbol')) // Asumo Bolivianos, puedes cambiarlo o quitarlo
                    ->alignEnd()
                    ->default(0),
                IconColumn::make('status')
                    ->label('Estado')
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'closed' => Heroicon::LockClosed,
                        'open' => Heroicon::LockOpen,
                    })
                    ->color(
                        fn (string $state): string => match ($state) {
                            'closed' => 'info',
                            'open' => 'warning',
                            default => 'gray',
                        }
                    ),
                
            ])
            ->recordActions([
                Action::make('show')
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
                    ->modalSubmitActionLabel('Cerrar caja'),
                    
                Action::make('exportPdf')
                    ->hiddenLabel(true)
                    ->icon("far-file-pdf")
                    ->url(fn (CashBoxClosing $record): string => route('cahsboxclosing.export.pdf', ["cbcId" => $record->id]))
                    ->openUrlInNewTab()
            ])
            ->filters([
                // Aquí puedes añadir filtros
            ])
            ->paginated(); // Habilitar paginación
    }
}
