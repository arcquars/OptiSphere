<?php

namespace App\Filament\BranchManager\Resources\Customers;

use App\Filament\BranchManager\Resources\Customers\Pages\CreateCustomer;
use App\Filament\BranchManager\Resources\Customers\Pages\EditCustomer;
use App\Filament\BranchManager\Resources\Customers\Pages\ListCustomers;
use App\Filament\BranchManager\Resources\Customers\Tables\CustomersTable;
// use App\Filament\BranchManager\Resources\Customers\Tables\CustomersTable;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Customer';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/{branch_id?}'),
            'create' => CreateCustomer::route('/create/{branch_id}'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

}
