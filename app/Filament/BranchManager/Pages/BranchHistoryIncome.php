<?php

namespace App\Filament\BranchManager\Pages;

use App\Models\CashBoxClosing;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseStockHistory;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class BranchHistoryIncome extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.branch-manager.pages.branch-history-income';

    protected static ?string $title = 'Historial de Ingresos';

    protected static bool $shouldRegisterNavigation = false;

    public $branch;
    public $codeBase;
    public $type;

    public static function getRoutePath(Panel $panel): string
    {
        return 'store/history-income/{branchId}/{codeBase}/{type}';
    }

    public function mount(int $branchId, string $codeBase, string $type): void
    {
        // Aquí puedes cargar la sucursal o hacer validaciones
        $this->branch = \App\Models\Branch::findOrFail($branchId);
        $this->codeBase = $codeBase;
        $this->type = $type;
    }

    protected function getViewData(): array
    {
        return [
            'branch' => $this->branch,
            'codeBase' => $this->codeBase,
            'type' => $this->type,
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
                WarehouseDelivery::query()
                ->where('base_code', $this->codeBase)
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
            ->recordActions([
                Action::make('exportPdf')
                    ->hiddenLabel(true)
                    ->icon("far-file-pdf")
                    ->url(fn (WarehouseDelivery $record): string => route(
                        'export.pdf.history.movement', 
                        [
                            "movement" => "ENTREGA",
                            "movement_id" => $record->id,
                            "type" => (strcmp($this->type, 'negative') === 0) ? '-' : '+'
                        ]))
                    ->openUrlInNewTab()
            ])
            ->filters([
                // Aquí puedes añadir filtros
            ])
            ->paginated(); // Habilitar paginación
    }
}
