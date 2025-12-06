<x-filament-panels::page>
    {{-- 1. Renderizamos el formulario del selector --}}
    {{ $this->form }}

    {{-- 2. Si se ha seleccionado un producto, cargamos el componente de edición --}}
    @if(!empty($data['selected_base_code']))
        <div class="mt-6">
            @livewire('generate-products-edit', ['baseCode' => $data['selected_base_code']], key('g-product-edit-' . $data['selected_base_code']))
        </div>
    @endif
</x-filament-panels::page>