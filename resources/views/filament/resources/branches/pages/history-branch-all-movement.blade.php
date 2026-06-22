<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Cabecera con los parámetros actuales --}}
        <div class="p-4 bg-white shadow rounded-xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Detalles de la Consulta</h2>
            <div class="flex flex-wrap gap-3 mt-3">
                <div class="flex items-center gap-2 px-3 py-1 bg-primary/10 text-primary rounded-full text-sm font-medium">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Sucursal: {{ $branch->name }}</span>
                </div>
            </div>
        </div>

        {{-- Tabla unificada de movimientos --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>