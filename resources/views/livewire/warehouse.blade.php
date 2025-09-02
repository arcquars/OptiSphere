<div>
    <form wire:submit="create">
        <div class="grid grid-cols-6 gap-4">
            <div class="col-span-5">
                {{ $this->form }}
            </div>
            <div class="col-span-1 flex flex-col justify-end">
                <button class="btn btn-primary btn-sm btn-block mb-1" type="submit">
                    Cargar
                </button>
            </div>
        </div>
    </form>
    <br>
    @if(count($cylinders) > 0)
    <div class="overflow-x-auto">
        <table class="table-auto border-collapse border border-gray-300 w-full text-center">
            <thead>
            <tr class="bg-green-500 text-white">
                <!-- Primera fila (encabezado) con 25 columnas -->
                <th class="border bg-white"></th>
                @foreach($cylinders as $cylinder)
                    <th class="border border-gray-300 p-1 text-xs">{{ number_format($cylinder->cylinder, 2) }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            <!-- Generamos 24 filas más -->
            <!-- Primera columna azul -->
            <!-- Ejemplo fila 2 -->
            <tr>
                <td class="border border-gray-300 p-1 bg-blue-500 text-white text-xs">0.00</td>
                <td class="border border-gray-300 p-1 text-xs">45</td>
            </tr>
            <tr>
                <td class="border border-gray-300 p-1 bg-blue-500 text-white text-xs">0.25</td>
                <td class="border border-gray-300 p-1 text-xs">25</td>
            </tr>

            <!-- Aquí deberías repetir lo mismo hasta la fila 25 -->
            </tbody>
        </table>
    </div>
    @endif

    <x-filament-actions::modals />
</div>
