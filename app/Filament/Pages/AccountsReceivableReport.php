<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Sale;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class AccountsReceivableReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.accounts-receivable-report';

    protected static string|\BackedEnum|null $navigationIcon = "c-banknotes";

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Cuentas por Cobrar';

    // (OPCIONAL) El texto que quieres en el menú.
    // Si no lo pones, usará el título (ej. "Mi Pagina Simple")
    public static function getNavigationLabel(): string
    {
        return __('Cuentas por cobrar');
    }


    /**
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Customer::query()
                    ->addSelect([
                        'total_credito_ventas' => Sale::selectRaw('SUM(final_total - paid_amount)')
                            // Aquí definimos la condición de la subconsulta
                            ->whereColumn('customer_id', 'customers.id') // Vincula con el cliente actual
                            ->where('status', Sale::SALE_STATUS_CREDIT) // Solo status CREDIT
                        // Si la columna 'customer_id' está en la tabla 'sales', usamos 'customer_id'
                        // Si la columna de unión en 'sales' es diferente, la usamos aquí.
                    ])
            )
            ->defaultSort('total_credito_ventas', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nit')
                    ->label('NIT')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_credito_ventas')
                    ->label('Total Comprado (Histórico)')
                    ->money('BOB') // Asumo Bolivianos, puedes cambiarlo o quitarlo
                    ->sortable()
                    ->alignEnd()
                    ->default(0),
            ])
            ->filters([
                // Aquí puedes añadir filtros
            ])
            ->actions([
                // Aquí puedes añadir acciones por fila
            ])
            ->bulkActions([
                // Aquí puedes añadir acciones masivas
            ])
            ->paginated(); // Habilitar paginación
    }
}
