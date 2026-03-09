<?php

namespace App\Filament\BranchManager\Resources\Customers\Pages;

use App\Filament\BranchManager\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
