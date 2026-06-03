<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Models\Warehouse;
use App\Models\WarehouseIncome;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class HistoryAllMovement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = WarehouseResource::class;

    public string $view = 'filament.resources.warehouses.pages.history-all-movement';

    // Parámetros recibidos por la URL
    public $wharehouse;
    public $wharehouse_id;

    public function mount($wharehouse_id): void
    {
        $this->wharehouse_id = $wharehouse_id;
        $this->wharehouse = Warehouse::find($wharehouse_id);
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
        $wh_id = $this->wharehouse_id;

        /**
         * Construimos un UNION de las tres fuentes principales de movimientos.
         * Esto nos da exactamente 1 fila por cada movimiento único en la base de datos,
         * eliminando la necesidad de usar 'groupBy' y previniendo el error 'only_full_group_by'.
         */
        $incomes = DB::table('warehouse_incomes')
            ->select(
                'id', 
                'income_date as date_movement', 
                DB::raw("'INGRESO' as movement_type"), 
                'user_id', 
                'warehouse_id',
                DB::raw("NULL as branch_id"),
            )
            ->where('warehouse_id', $wh_id);

        $deliveries = DB::table('warehouse_deliveries')
            ->select(
                'id', 
                'delivery_date as date_movement', 
                DB::raw("'ENTREGA_SUCURSAL' as movement_type"), 
                'user_id', 
                'warehouse_id',
                'branch_id',
            )
            ->where('warehouse_id', $wh_id);

        $refunds = DB::table('warehouse_refunds')
            ->select(
                'id', 
                'refund_date as date_movement', 
                DB::raw("'DEVOLUCION' as movement_type"), 
                'user_id', 
                'warehouse_id',
                'branch_id',
            )
            ->where('warehouse_id', $wh_id);

        // Unimos todas las tablas en una sola consulta virtual
        $unionQuery = $incomes->unionAll($deliveries)->unionAll($refunds);

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
            ])
            ->filters([
                // Filtros opcionales
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