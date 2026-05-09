<?php

namespace App\Filament\BranchManager\Pages;

use App\Models\Branch;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use App\Models\WarehouseDelivery;
use Filament\Tables\Table;
use Filament\Actions\Action;

class BranchAllHistoryIncomePage extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.branch-manager.pages.branch-all-history-income-page';

    protected static bool $shouldRegisterNavigation = false;

    public Branch $branch;

    public static function getRoutePath(Panel $panel): string
    {
        return 'store/history-income/{branchId}';
    }

    public function getTitle(): string
    {
        return ($this->branch->name ?? 'Sucursal') . " - Historial de Ingresos";
    }

    public function mount(int $branchId): void
    {
        $this->branch = Branch::findOrFail($branchId);
    }

    /**
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                WarehouseDelivery::query()
                ->where('branch_id', $this->branch->id)
            )
            ->extraAttributes([
                'class' => 'tabla-cerisier shadow-md border-separate border-spacing-0',
            ])
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Fecha Entrega')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_code')
                    ->label('Codigo Base'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Almacén')
                    ->searchable()
                    ->sortable(),
            ])
            ->reorderable('delivery_date', direction: 'desc')
            ->recordActions([
                Action::make('details')
                    ->hiddenLabel(true)
                    ->icon("far-eye")
                    ->url(fn (WarehouseDelivery $record): string => route(
                        'filament.branch-manager.pages.income-details-page', 
                        [
                            "warehouseDeliveryId" => $record->id
                        ]))
            ])
            ->filters([
                // Aquí puedes añadir filtros
            ])
            ->paginated(); // Habilitar paginación
    }
}
