<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Models\Warehouse;
use App\Models\WarehouseIncome;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class HistoryMovement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = WarehouseResource::class;

    public string $view = 'filament.resources.warehouses.pages.history-movement';

    // Parámetros recibidos por la URL
    public $wharehouse;
    public $wharehouse_id;
    public $type;
    public $code;

    public function mount($wharehouse_id, $type, $code): void
    {
        $this->wharehouse_id = $wharehouse_id;
        $this->wharehouse = Warehouse::find($wharehouse_id);
        $this->type = $type;
        $this->code = $code;
    }

    /**
     * Título dinámico basado en el código del producto o base seleccionado.
     */
    public function getTitle(): string 
    {
        return "Historial de Movimientos: {$this->code}";
    }

    /**
     * Configuración de la tabla uniendo los 3 modelos (Ingresos, Entregas y Devoluciones).
     */
    public function table(Table $table): Table
    {
        $wh_id = $this->wharehouse_id;

        // 1. Construimos las consultas individuales con SELECT consistentes
        $incomes = DB::table('warehouse_incomes')
            ->select(
                'id', 
                'income_date as date', 
                DB::raw("'INGRESO' as movement_label"), 
                'user_id',
                'base_code', 
                'warehouse_id', 
                DB::raw("NULL as branch_id"),
                DB::raw("CASE 
                    WHEN base_code IS NOT NULL THEN (
                        SELECT op.type 
                        FROM optical_properties op 
                        INNER JOIN products p ON p.id = op.product_id
                        INNER JOIN warehouse_stocks ws ON  ws.product_id = p.id 
                        INNER JOIN warehouse_stock_histories wsh ON wsh.warehouse_stock_id = ws.id
                        WHERE wsh.movement_type='INGRESO' AND wsh.type_id = warehouse_incomes.id 
                        LIMIT 1
                    ) 
                    ELSE NULL 
                END as op_type")
            )
            ->where('warehouse_id', $wh_id);

        $deliveries = DB::table('warehouse_deliveries')
            ->select(
                'id', 
                'delivery_date as date', 
                DB::raw("'ENTREGA' as movement_label"), 
                'user_id', 
                'base_code',
                'warehouse_id', 
                'branch_id',
                DB::raw("CASE 
                    WHEN base_code IS NOT NULL THEN (
                        SELECT op.type 
                        FROM optical_properties op 
                        INNER JOIN products p ON p.id = op.product_id
                        INNER JOIN warehouse_stocks ws ON  ws.product_id = p.id 
                        INNER JOIN warehouse_stock_histories wsh ON wsh.warehouse_stock_id = ws.id
                        WHERE wsh.movement_type='ENTREGA_SUCURSAL' AND wsh.type_id = warehouse_deliveries.id 
                        LIMIT 1
                    ) 
                    ELSE NULL 
                END as op_type")
            )
            ->where('warehouse_id', $wh_id);

        $refunds = DB::table('warehouse_refunds')
            ->select(
                'id', 
                'refund_date as date', 
                DB::raw("'DEVOLUCION' as movement_label"), 
                'user_id', 
                'base_code',
                'warehouse_id', 
                'branch_id',
                DB::raw("CASE 
                    WHEN base_code IS NOT NULL THEN (
                        SELECT op.type 
                        FROM optical_properties op 
                        INNER JOIN products p ON p.id = op.product_id
                        INNER JOIN warehouse_stocks ws ON  ws.product_id = p.id 
                        INNER JOIN warehouse_stock_histories wsh ON wsh.warehouse_stock_id = ws.id
                        WHERE wsh.movement_type='DEVOLUCION' AND wsh.type_id = warehouse_refunds.id 
                        LIMIT 1
                    ) 
                    ELSE NULL 
                END as op_type")
            )
            ->where('warehouse_id', $wh_id);

        // 2. Unimos las consultas mediante UNION ALL
        $unionQuery = $incomes->unionAll($deliveries)->unionAll($refunds);

        return $table
            ->query(
                /**
                 * SOLUCIÓN AL ERROR DE COLUMNA NO ENCONTRADA:
                 * * Al usar WarehouseIncome::query(), Laravel asume que la tabla es 'warehouse_incomes'.
                 * Al ordenar, añade automáticamente 'warehouse_incomes.id'.
                 * * Usamos setTable('history') en una nueva instancia del modelo para que Laravel 
                 * use 'history.id' como referencia de columna en lugar de la tabla original.
                 */
                (new WarehouseIncome())->setTable('history')
                    ->newQuery()
                    ->fromSub($unionQuery, 'history')
                    // Joins sobre la subconsulta 'history'
                    ->leftJoin('users', 'history.user_id', '=', 'users.id')
                    ->leftJoin('branches', 'history.branch_id', '=', 'branches.id')
                    ->select('history.*', 'users.name as user_name', 'branches.name as branch_name')
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_label')
                    ->label('Tipo de Acción')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'INGRESO' => 'success',
                        'ENTREGA' => 'info',
                        'DEVOLUCION' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('base_code')
                    ->label('Codigo Base'),
                Tables\Columns\TextColumn::make('op_type')
                    ->label('Tipo')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('user_name')
                    ->label('Registrado por'),
                Tables\Columns\TextColumn::make('branch_name')
                    ->label('Sucursal Relacionada')
                    ->placeholder('N/A (Ingreso Directo)')
                    ->default('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('movement_label')
                    ->label('Filtrar Acción')
                    ->options([
                        'INGRESO' => 'Ingresos',
                        'ENTREGA' => 'Entregas',
                        'DEVOLUCION' => 'Devoluciones',
                    ]),
            ])
            ->recordActions([
                Action::make('view')
                ->label("Ver")
                ->visible(fn ($record) => $record->op_type !== null)
                ->url(fn (WarehouseIncome $record): 
                    string => route(
                        'filament.admin.resources.warehouses.history.show', 
                        ["history_id" => $record->id, "action" => $record->movement_label, "type" => $record->op_type])
                    )
                ->openUrlInNewTab()
            ])
            ->defaultSort('date', 'desc');
    }
}