<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Filament\Exports\SalePaymentExporter;
use App\Models\PagoQr;
use App\Services\CreditService;
use App\Services\EconomicoApiService;
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
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\View as ComponentsView;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;

class CreditPaymentResource extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.credit-payment-resource';

    protected static string|\BackedEnum|null $navigationIcon = "c-banknotes";

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pagos a cuenta de Ventas a crédito';

    public ?int $branchId = null;

    protected static ?string $slug = 'credit-payment-resource/{branchId?}';

    public function mount(?int $branchId = null): void
    {
        $this->branchId = $branchId;
    }

    public static function getNavigationLabel(): string
    {
        return __('Pagos a cuenta de Ventas');
    }

    /**
     * Configuración de la tabla.
     */
    public function table2(Table $table): Table
    {
        $branchId = $this->branchId;
        $query = SalePayment::query()
                    ->where('deleted', false)
                    ->whereIn('id', function ($subquery) {
                        $subquery->selectRaw('MAX(id)')
                                ->from('sale_payments')
                                ->where('deleted', false)
                                ->groupBy('sale_id');
                    })
                    ->whereHas('sale', function ($query) use ($branchId){
                        $query->where('sales.status', Sale::SALE_STATUS_CREDIT);
                        if($branchId){
                            $query->where('sales.branch_id', $branchId);
                        }
                    });
        if($this->branchId){
            $query->where('branch_id', $this->branchId);
        }
        return $table
            ->query(
                $query
            )
            ->extraAttributes([
                'class' => 'tabla-compact',
                'x-data' => '', // necesario para que Alpine no interfiera
            ])
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
                TextColumn::make('sale.id')
                    ->label('ID'),
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
                    ->label('Usuario')
                    ->visibleFrom('xl'),
                TextColumn::make('amount')
                    ->label('Pago')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),
                TextColumn::make('payment_method')
                    ->label('Método de pago')
                    ->visibleFrom('xl'),
                TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->visibleFrom('xl'),
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

                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
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
            ->recordActions([
                Action::make('view_qr')
                    ->label('')
                    ->icon('fas-qrcode')
                    ->visible(fn ($record) => $record->sale->final_total != $record->sale->paid_amount)
                    ->modalHeading('Generar QR de pago')
                    ->modalWidth(Width::Small)
                    ->fillForm(function (SalePayment $record): array {
                        $creditService = new CreditService();
                        $pagoQr = $creditService->getQrPartialPayment($record->sale_id);
                        $pagoQrId = 0;
                        $qrId = "";
                        $qrImage = "";
                        if($pagoQr){
                            $pagoQrId = $pagoQr->id;
                            $qrId = $pagoQr->qr_id;
                            $qrImage = $pagoQr->qr_image;
                        }
                        return [
                            'pagoQrId' => $pagoQrId,
                            'qrId' => $qrId,
                            'qrImage' => $qrImage
                        ];
                    })
                    ->schema([
                        Image::make(url: 'qr_preview', alt: 'Qr de pago')
                            ->url(fn ($get) => $get('qrImage') 
                                ? 'data:image/png;base64,' . $get('qrImage') 
                                : asset('img/cerisier-no-image.png')
                            )
                            ->imageSize('18rem')
                            ->alignCenter(),
                        Hidden::make('qrId'),
                        Hidden::make('pagoQrId'),
                        ComponentsView::make('filament.pages.actions.sale-payment-qr')
                    ])
                    ->action(function (array $data, SalePayment $record): void {
                        
                        $apiService = new EconomicoApiService($record->sale->branch_id);
                        $apiService->cancelQr($data['qrId']);

                        $sale = Sale::find($record->sale_id);
                        $sale->partialQrPayments()->updateExistingPivot($data['pagoQrId'], [
                            'status' => PagoQr::STATUS_CANCELLED,
                        ]);

                        Notification::make()
                            ->title('Se ANULO correctamente el QR')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel("Cancelar QR")
                    ->extraModalFooterActions([
                        Action::make('verify_qr_payment')
                            ->label('Verificar pago')
                            ->icon('heroicon-o-check-circle')
                            ->color('warning')
                            ->action(function (array $data, SalePayment $record, Action $action): void {
                                $parentData = $action
                                    ->getLivewire()
                                    ->getMountedTableActionForm()
                                    ->getState();

                                $qrId     = $parentData['qrId']     ?? null;
                                $pagoQrId = $parentData['pagoQrId'] ?? null;
                                if (empty($qrId)) {
                                    Notification::make()
                                        ->title('No hay QR activo para verificar')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                $apiService = new EconomicoApiService($record->sale->branch_id);
                                $result = $apiService->checkQrStatus($qrId);

                                if ($result['success'] && $result['estado']) {
                                    $saldo = $record->sale->final_total - $record->sale->paid_amount;

                                    $creditService = new CreditService();
                                    $creditService->registerPayment(
                                        $saldo,
                                        $saldo,
                                        $record->sale,
                                        'QR',
                                        auth()->id(),
                                        $qrId,
                                    );

                                    Notification::make()
                                        ->title('¡Pago QR confirmado y registrado!')
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Pago aún no realizado')
                                        ->body($result['message'] ?? 'El cliente no ha escaneado el QR.')
                                        ->warning()
                                        ->send();
                                }
                            }),
                    ])
                    ->color('success')
                    ->modalCancelActionLabel('Cerrar'),
                Action::make('view_history')
                    ->label('')
                    ->icon('far-clock')
                    ->modalHeading('Historial y Registro de Abonos')
                    ->modalContent(function (SalePayment $record) {
                        $sale = Sale::with(['partialQrPayments' => function ($query) {
                            $query->wherePivot('status', 'PENDING');
                        }])->find($record->sale_id);
                        $isQrActive = false;
                        if(count($sale->partialQrPayments) > 0){
                            $isQrActive = true;
                        }
                        $payments = SalePayment::where('sale_id', $record->sale_id)->get();

                        return view(
                            'filament.pages.actions.sale-payment-history',
                            ['record' => $record, 'isQrActive' => $isQrActive, 'payments' => $payments],
                        );
                    })
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
                        $sale = Sale::with(['partialQrPayments' => function ($query) {
                            $query->wherePivot('status', 'PENDING');
                        }])->find($record->sale_id);
                        if(count($sale->partialQrPayments) > 0){
                            Notification::make()
                                ->title('Actualmente existe un QR de pago vigente, si quiere registrar pago Anule el qr.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $creditService = new CreditService();
                        $creditService->registerPayment(
                            $data['amount'],
                            $record->residue,
                            $record->sale,
                            $data['payment_method'],
                            auth()->id(),
                            null
                        );
                        Notification::make()
                            ->title('Pago registrado correctamente')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitAction(fn (SalePayment $record) => $record->sale->is_paid ? false : null)
                    ->modalSubmitActionLabel('Registrar pago')
                    ->modalCancelActionLabel('Cerrar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                    BulkAction::make('paymentGroup')
                        ->label("Pagar grupo")
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $creditService = new CreditService();
                            foreach($records as $record){
                                $creditService->registerPayment(
                                    $record->residue,
                                    $record->residue,
                                    $record->sale,
                                    SalePayment::METHOD_CASH,
                                    auth()->id(),
                                    "Registrado mediante por: Grupo de Pagos"
                                );
                            }
                            
                            Notification::make()
                                ->title('Pagos registrados correctamente')
                                ->success()
                                ->send();
                        })
                        ->modalContent(function (Collection $records) {
                            return view(
                                'filament.pages.actions.sale-payment-sales',
                                ['records' => $records]
                            );
                        }),
                ]),
                
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (SalePayment $record): bool => !$record->sale->is_paid,
            )
            ->paginated();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        $currentPanel = filament()->getCurrentPanel()?->getId();
        
        // Obtenemos el ID de la sucursal de la URL actual
        $requestedBranchId = request()->route('branchId');

        if ($currentPanel === 'branch-manager') {
            $hasRole = $user->hasRole('branch-manager') || $user->hasRole('admin');
            
            // Si hay un ID en la URL, validar que el manager tenga acceso a esa sucursal específica
            if ($requestedBranchId && !$user->hasRole('admin')) {
                return $hasRole && $user->branches()->where('branches.id', $requestedBranchId)->exists();
            }

            return $hasRole;
        }

        return $user->hasRole('admin');
    }

    /**
     * Configuración de la tabla.
     */
    public function table(Table $table): Table
    {
        // $branchId = $this->branchId;
        // $query = SalePayment::query()
        //             ->where('deleted', false)
        //             ->whereIn('id', function ($subquery) {
        //                 $subquery->selectRaw('MAX(id)')
        //                         ->from('sale_payments')
        //                         ->where('deleted', false)
        //                         ->groupBy('sale_id');
        //             })
        //             ->whereHas('sale', function ($query) use ($branchId){
        //                 $query->where('sales.status', Sale::SALE_STATUS_CREDIT);
        //                 if($branchId){
        //                     $query->where('sales.branch_id', $branchId);
        //                 }
        //             });

        $query = Sale::query()->where('status', Sale::SALE_STATUS_CREDIT);
        if($this->branchId){
            $query->where('branch_id', $this->branchId);
        }
        
        return $table
            ->query(
                $query
            )
            ->extraAttributes([
                'class' => 'tabla-compact',
                'x-data' => '', // necesario para que Alpine no interfiera
            ])
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
                TextColumn::make('id')
                    ->label('ID'),
                TextColumn::make('date_sale')
                    ->label('Fecha de venta')
                    ->date('Y-m-d')
                    // ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    // ->searchable()
                    ->sortable(),
                TextColumn::make('final_total')
                    ->label('Monto de venta')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),
                TextColumn::make('created_at')
                    ->label('Fecha de pago')
                    ->date('Y-m-d'),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->visibleFrom('xl'),
                TextColumn::make('lastPayment.amount')
                    ->label('Pago')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),
                TextColumn::make('payment_method')
                    ->label('Método de pago')
                    ->visibleFrom('xl'),
                TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->visibleFrom('xl'),
                TextColumn::make('lastPayment.residue')
                    ->label('Saldo')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),
                Tables\Columns\IconColumn::make('is_paid')
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

                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
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
            ->recordActions([
                Action::make('view_qr')
                    ->label('')
                    ->icon('fas-qrcode')
                    ->visible(fn ($record) => $record->final_total != $record->paid_amount)
                    ->modalHeading('Generar QR de pago')
                    ->modalWidth(Width::Small)
                    ->fillForm(function (Sale $record): array {
                        $creditService = new CreditService();
                        $pagoQr = $creditService->getQrPartialPayment($record->id);
                        $pagoQrId = 0;
                        $qrId = "";
                        $qrImage = "";
                        if($pagoQr){
                            $pagoQrId = $pagoQr->id;
                            $qrId = $pagoQr->qr_id;
                            $qrImage = $pagoQr->qr_image;
                        }
                        return [
                            'pagoQrId' => $pagoQrId,
                            'qrId' => $qrId,
                            'qrImage' => $qrImage
                        ];
                    })
                    ->schema([
                        Image::make(url: 'qr_preview', alt: 'Qr de pago')
                            ->url(fn ($get) => $get('qrImage') 
                                ? 'data:image/png;base64,' . $get('qrImage') 
                                : asset('img/cerisier-no-image.png')
                            )
                            ->imageSize('18rem')
                            ->alignCenter(),
                        Hidden::make('qrId'),
                        Hidden::make('pagoQrId'),
                        ComponentsView::make('filament.pages.actions.sale-payment-qr')
                    ])
                    ->action(function (array $data, Sale $record): void {
                        
                        $apiService = new EconomicoApiService($record->branch_id);
                        $apiService->cancelQr($data['qrId']);

                        $sale = Sale::find($record->id);
                        $sale->partialQrPayments()->updateExistingPivot($data['pagoQrId'], [
                            'status' => PagoQr::STATUS_CANCELLED,
                        ]);

                        Notification::make()
                            ->title('Se ANULO correctamente el QR')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel("Cancelar QR")
                    ->extraModalFooterActions([
                        Action::make('verify_qr_payment')
                            ->label('Verificar pago')
                            ->icon('heroicon-o-check-circle')
                            ->color('warning')
                            ->action(function (array $data, Sale $record, Action $action): void {
                                $parentData = $action
                                    ->getLivewire()
                                    ->getMountedTableActionForm()
                                    ->getState();

                                $qrId     = $parentData['qrId']     ?? null;
                                $pagoQrId = $parentData['pagoQrId'] ?? null;
                                if (empty($qrId)) {
                                    Notification::make()
                                        ->title('No hay QR activo para verificar')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                $apiService = new EconomicoApiService($record->branch_id);
                                $result = $apiService->checkQrStatus($qrId);

                                if ($result['success'] && $result['estado']) {
                                    $saldo = $record->final_total - $record->paid_amount;

                                    $creditService = new CreditService();
                                    $creditService->registerPayment(
                                        $saldo,
                                        $saldo,
                                        $record,
                                        'QR',
                                        auth()->id(),
                                        $qrId,
                                    );

                                    Notification::make()
                                        ->title('¡Pago QR confirmado y registrado!')
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Pago aún no realizado')
                                        ->body($result['message'] ?? 'El cliente no ha escaneado el QR.')
                                        ->warning()
                                        ->send();
                                }
                            }),
                    ])
                    ->color('success')
                    ->modalCancelActionLabel('Cerrar'),
                Action::make('view_history')
                    ->label('')
                    ->icon('far-clock')
                    ->modalHeading('Historial y Registro de Abonos')
                    ->modalContent(function (Sale $record) {
                        $sale = Sale::with(['partialQrPayments' => function ($query) {
                            $query->wherePivot('status', 'PENDING');
                        }])->find($record->id);
                        $isQrActive = false;
                        if(count($sale->partialQrPayments) > 0){
                            $isQrActive = true;
                        }
                        $payments = SalePayment::where('sale_id', $record->id)->get();

                        return view(
                            'filament.pages.actions.sale-payment-history',
                            ['record' => $record, 'isQrActive' => $isQrActive, 'payments' => $payments],
                        );
                    })
                    // Definición del esquema condicional
                    ->schema(fn (Sale $record): array => $record->is_paid 
                        ? [] 
                        : [
                            Grid::make(2)->schema([
                                TextInput::make('amount')
                                    ->label('Monto del abono')
                                    ->numeric()
                                    ->step('0.01')
                                    ->required()
                                    ->prefix(config('cerisier.currency_symbol', '$'))
                                    ->maxValue(fn (Sale $record) => number_format($record->final_total - $record->paid_amount, 2))
                                    ->hint(fn (Sale $record) => 'Saldo pendiente: ' . number_format($record->final_total - $record->paid_amount, 2)),
                                
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
                    ->action(function (array $data, Sale $record): void {
                        $sale = Sale::with(['partialQrPayments' => function ($query) {
                            $query->wherePivot('status', 'PENDING');
                        }])->find($record->id);
                        if(count($sale->partialQrPayments) > 0){
                            Notification::make()
                                ->title('Actualmente existe un QR de pago vigente, si quiere registrar pago Anule el qr.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $creditService = new CreditService();
                        $creditService->registerPayment(
                            $data['amount'],
                            $record->lastPayment? $record->lastPayment->residue : $record->final_total,
                            $record,
                            $data['payment_method'],
                            auth()->id(),
                            null
                        );
                        Notification::make()
                            ->title('Pago registrado correctamente')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitAction(fn (Sale $record) => $record->is_paid ? false : null)
                    //->modalSubmitAction(fn (SalePayment $record) => $record->sale->is_paid ? false : null)
                    ->modalSubmitActionLabel('Registrar pago')
                    ->modalCancelActionLabel('Cerrar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                    BulkAction::make('paymentGroup')
                        ->label("Pagar grupo")
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $creditService = new CreditService();
                            foreach($records as $record){
                                $creditService->registerPayment(
                                    $record->residue,
                                    $record->residue,
                                    $record->sale,
                                    SalePayment::METHOD_CASH,
                                    auth()->id(),
                                    "Registrado mediante por: Grupo de Pagos"
                                );
                            }
                            
                            Notification::make()
                                ->title('Pagos registrados correctamente')
                                ->success()
                                ->send();
                        })
                        ->modalContent(function (Collection $records) {
                            return view(
                                'filament.pages.actions.sale-payment-sales',
                                ['records' => $records]
                            );
                        }),
                ]),
                
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Sale $record): bool => !$record->is_paid,
            )
            ->paginated();
    }
}