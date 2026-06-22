<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Warehouse;
use App\Models\WarehouseIncome;
use App\Models\WarehouseStockHistory;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class HistoryBranchAllMovement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = BranchResource::class;

    public string $view = 'filament.resources.branches.pages.history-branch-all-movement';

    // Parámetros recibidos por la URL
    public Branch $branch;
    public int $branch_id;

    public function mount(int $branch_id): void
    {
        $this->branch_id = $branch_id;
        $this->branch = Branch::find($branch_id);
    }

    /**
     * Título dinámico basado en el código del producto o base seleccionado.
     */
    public function getTitle(): string 
    {
        return "Historial de Todos los Movimientos";
    }

    public function table(Table $table): Table
    {
        $branch_id = $this->branch_id;

        $deliveries = DB::table('warehouse_deliveries')
            ->select(
                'id', 
                'delivery_date as date_movement', 
                DB::raw("'ENTREGA_SUCURSAL' as movement_type"), 
                'user_id', 
                'warehouse_id',
                'branch_id',
                'status'
            )
            ->where('branch_id', $branch_id);

        $refunds = DB::table('warehouse_refunds')
            ->select(
                'id', 
                'refund_date as date_movement', 
                DB::raw("'DEVOLUCION' as movement_type"), 
                'user_id', 
                'warehouse_id',
                'branch_id',
                'status'
            )
            ->where('branch_id', $branch_id);

        // Unimos todas las tablas en una sola consulta virtual
        $unionQuery = $deliveries->unionAll($refunds);

        return $table
            ->query(function () use ($unionQuery) {
                // 1. Construimos la consulta base en Query Builder puro
                $rawQuery = DB::table('warehouse_incomes')
                    ->fromSub($unionQuery, 'history_movements')
                    ->leftJoin('users', 'history_movements.user_id', '=', 'users.id')
                    ->leftJoin('branches', 'history_movements.branch_id', '=', 'branches.id')
                    ->select('history_movements.*', 'users.name as user_name', 'branches.name as branch_name');

                // 2. Envolvemos la query dentro de un Eloquent Builder legítimo mapeando un Modelo
                return (new WarehouseIncome())
                    ->setTable('history_movements')
                    ->newQuery()
                    ->setQuery($rawQuery); // Esto fuerza el tipado correcto que Filament exige
            })
            ->columns([
                TextColumn::make('id')
                    ->label('Número')
                    ->sortable(),
                    
                TextColumn::make('date_movement')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                TextColumn::make('movement_type')
                    ->label('Tipo de Acción')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'INGRESO' => 'success',
                        'ENTREGA_SUCURSAL' => 'info',
                        'DEVOLUCION' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('user_name')
                    ->label('Registrado por')
                    ->placeholder('Sistema'),
                TextColumn::make('branch_name')
                    ->label('Sucursal Relacionada')
                    ->placeholder('N/A (Ingreso Directo)')
                    ->default('-'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("statuses.{$state}"))
                    ->alignCenter()
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'VOID' => 'danger',
                    })
            ])
            ->filters([
                // Filtros opcionales movement_type
                SelectFilter::make('movement_type')
                ->options([
                    // 'INGRESO' => 'INGRESO',
                    'ENTREGA_SUCURSAL' => 'ENTREGA_SUCURSAL',
                    'DEVOLUCION' => 'DEVOLUCION',
                ]),
                Filter::make('date_movement')
                ->schema([
                    DatePicker::make('created_from')->label('Desde'),
                    DatePicker::make('created_until')->label('Hasta'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('date_movement', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('date_movement', '<=', $date),
                        );
                })
            ])
            ->recordActions([
                Action::make('view')
                ->label("Ver")
                // ->visible(fn ($record) => $record->op_type !== null)
                ->url(fn (WarehouseIncome $record): 
                    string => WarehouseResource::getUrl(
                        'history.movement.show', 
                        ["history_id" => $record->id, "action" => $record->movement_type])
                    )
                ->openUrlInNewTab()
            ])
            // Ordenamos por la columna de fecha virtualizada de la subconsulta
            ->defaultSort('date_movement', 'desc');
    }
}