<div class="flex items-center justify-between bg-gray-100 dark:bg-gray-800 p-4 rounded-xl">
    <div class="w-full">
        <h1 class="fi-header-heading">
            Terminal de Venta - {{ $branch->name }}
        </h1>
        <div class="flex justify-between items-center w-full">
            @if($isOpenCashBox)
            <p class="text-success">
                Gestionando ventas para la sucursal seleccionada <i class="fa-solid fa-lock-open"></i>
            </p>
            <div class="flex justify-end">
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-sm btn-primary m-1">Historial Entregas <i class="fa-solid fa-circle-arrow-down"></i></div>
                    <ul tabindex="-1" class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm">
                        <li>
                            <a x-on:click="$dispatch('toggle-history-income'); document.activeElement.blur()">Historial Por lente</a>
                        </li>
                        <li><a href="{{ route('filament.branch-manager.pages.branch-all-history-income-page', ['branchId' => $branch->id]) }}">Todo</a></li>
                    </ul>
                </div>
                <a 
                    href="{{ App\Filament\BranchManager\Resources\Customers\Pages\ListCustomers::getUrl(['branch_id' => $branch->id]) }}" 
                    class="btn btn-sm btn-primary mt-1 mx-1">
                    Adm. Clientes
                </a>
                <a 
                    href="{{ url('branch-manager/credit-payment-resource/'. $branch->id) }}" 
                    class="btn btn-sm btn-primary mt-1 mx-1">
                    Adm. Ventas a credito
                </a>
                @if($isFacturable)
                <livewire:branch.siat-connection-status :branch="$branch" />
                @endif
            </div>
            @else
            <p class="text-error">Caja se encuentra cerrada <i class="fa-solid fa-lock"></i></p>
            @endif
        </div>  
    </div>
</div>