<x-filament-panels::page>
    <div class="grid grid-flow-col grid-rows-1 gap-2">
        <div class="col-span-1">
            <dl>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <dt class="text-sm font-semibold text-blue-700">Sucursal</dt>
                    <dd class="text-lg text-blue-900">{{ $branch->name }}</dd>
                </div>
            </dl>
        </div>
        <div class="col-span-1">
            <dl>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <dt class="text-sm font-semibold text-blue-700">Direccion</dt>
                    <dd class="text-lg text-blue-900">{{ $branch->address }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <livewire:manage-branch :branch-id="$branch->id" />
</x-filament-panels::page>
