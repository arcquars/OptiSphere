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
            <div>
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