<x-filament-panels::page>
    <div class="p-4 bg-white shadow rounded-xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
        <h5>Sucursal: {{ $branch->name }}</h5>
        <div class="flex flex-wrap gap-3 mt-3">
            <div class="flex items-center gap-2 badge badge-soft @if(strcmp($type, 'positive') == 0) badge-success @else badge-error @endif">
                <i class="fa-solid fa-tags"></i>
                <span>Tipo: {{ $type }}</span>
            </div>
            <div class="flex items-center gap-2 badge badge-soft badge-info">
                <i class="fa-solid fa-barcode"></i>
                <span>Código: {{ $codeBase }}</span>
            </div>
        </div>
    </div>

    {{ $this->table }}

</x-filament-panels::page>