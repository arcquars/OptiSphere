<x-filament-panels::page>
    <div class="grid items-center grid-cols-2 gap-1">
        <div>
            <p>Sucursal: <b>{{ $sucursalId }}</b> <i class="fa-solid fa-grip-lines-vertical"></i> Punto de venta <b>{{ $puntoVentaId }}</i></b></p>
        </div>
        <div class="justify-self-end">
            <ul 
                class="menu menu-vertical lg:menu-horizontal rounded-box
                bg-warning text-warning-content p-0 m-0
                "
            >
                <li>
                    <a 
                        href="{{ $urlSiatConfig }}"
                        class="hover:bg-warning-focus active:bg-warning-focus"
                    >
                        Configurar SIAT
                    </a>
                </li>
                <li>
                    <a class="hover:bg-warning-focus active:bg-warning-focus">
                        Manejo de Eventos
                    </a>
                </li>
            </ul>
        </div>
        
    </div>
    
    
    <div class="flex border border-gray-200 p-2">
        <livewire:siat-manager.menu-siat />

        <!-- DIV 2: Ocupa el RESTANTE -->
        <div class="flex-grow">
            <livewire:siat-manager.switch-siat :active="1" :branchId="$branch->id" />
        </div>
    </div>
</x-filament-panels::page>
