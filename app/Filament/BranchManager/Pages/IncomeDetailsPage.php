<?php

namespace App\Filament\BranchManager\Pages;

use App\Filament\Resources\Warehouses\Pages\HistoryMovement;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseStockHistory;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;

class IncomeDetailsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.branch-manager.pages.income-details-page';

    protected static ?string $title = 'Historial de Ingresos';
    
    protected static bool $shouldRegisterNavigation = false;

    public $warehouseDelivery;

    public static function getRoutePath(Panel $panel): string
    {
        return 'store/income-details/{warehouseDeliveryId}';
    }

    public function getTitle(): string
    {
        return ($this->warehouseDelivery->branch->name ?? 'Sucursal') . " - Detalle de Ingresos";
    }

    public function mount(int $warehouseDeliveryId): void
    {
        $this->warehouseDelivery = WarehouseDelivery::findOrFail($warehouseDeliveryId);
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/branch-manager/store/history-income/' . $this->warehouseDelivery->branch->id => 'Historial de Ingresos',
            "Detalle - {$this->warehouseDelivery->branch->name}",
        ];
    }

    /**
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                WarehouseStockHistory::query()
                ->where('movement_type', WarehouseStockHistory::MOVEMENT_TYPE_DELIVERY)
                ->where('type_id', $this->warehouseDelivery->id)
                ->has('warehouseStock')
            )
            ->extraAttributes([
                'class' => 'tabla-cerisier shadow-md border-separate border-spacing-0',
            ])
            // ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('warehouseStock.product.id')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('warehouseStock.product.name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouseStock.product.code')
                    ->label('Código')
                    ->searchable(),
                Tables\Columns\TextColumn::make('old_quantity')
                    ->label('Cantidad Antigua')
                    ->numeric()->alignEnd(),
                Tables\Columns\TextColumn::make('new_quantity')
                    ->label('Cantidad Nueva')
                    ->numeric()->alignEnd(),
                Tables\Columns\TextColumn::make('difference')
                    ->label('Diferencia')
                    ->numeric()
                    ->alignEnd(),
                // Tables\Columns\TextColumn::make('delivery_date')
                //     ->label('Fecha Entrega')
                //     ->dateTime('d/m/Y H:i')
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('base_code')
                //     ->label('Codigo Base'),
                // Tables\Columns\TextColumn::make('user.name')
                //     ->label('Usuario')
                //     ->searchable()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('warehouse.name')
                //     ->label('Almacén')
                //     ->searchable()
                //     ->sortable(),
            ])
            ->recordActions([
                // Action::make('details')
                //     ->hiddenLabel(true)
                //     ->icon("far-eye")
                //     ->url(fn (WarehouseDelivery $record): string => route(
                //         'filament.branch-manager.pages.income-details-page', 
                //         [
                //             "warehouseDeliveryId" => $record->id
                //         ]))
            ])
            ->filters([
                // Aquí puedes añadir filtros
            ])
            ->paginated(); // Habilitar paginación
    }
}
