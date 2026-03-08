<div>
    <div class="modal {{ $isOpen ? 'modal-open' : '' }}" role="dialog">
        <div class="modal-box max-w-2xl border-t-4 border-primary shadow-2xl">
            @if($product)
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="font-bold text-2xl text-base-content">Editar entrega ({{ $product->name }})</h3>
                    </div>
                    <button wire:click="closeModal" class="btn btn-sm btn-circle btn-ghost">✕</button>
                </div>

                <p>Cantidad actual en inventario: <b>{{ $this->warehouseStockHistory->warehouseStock->quantity }}</b></p>
                <p>Cantidad registrada en esta entrada: <b>{{ $this->warehouseStockHistory->difference }}</b></p>
                <p>Minimo permitido segun stock actual: <b>{{ $this->minAmount }}</b> 
                </p>
                {{-- <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Cantidad</label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="number"
                        wire:model="amount"
                    />
                </x-filament::input.wrapper> --}}
                <fieldset class="fieldset" data-validator>
                    <legend class="fieldset-legend">Cantidad</legend>
    
                    <input 
                        type="number" 
                        class="input input-sm input-bordered @error('amount') input-error @endif" 
                        wire:model.prevent="amount"
                        {{-- wire:blur="validate('amount')" --}}
                        placeholder="Ingresa un número"
                    />
                    
                    @error('amount')
                        <p class="label-text-alt text-error mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>
            @else
                <div class="flex flex-col items-center py-10">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                    <p class="mt-4 text-sm opacity-50">Cargando información del producto...</p>
                </div>
            @endif

            <div class="modal-action">
                <button wire:click="updateRegister" class="btn btn-info btn-sm btn-outline">Grabar cambio</button>
                <button wire:click="closeModal" class="btn btn-sm">Cerrar</button>
            </div>
        </div>


        {{-- Overlay para cerrar al hacer clic fuera --}}
        <div class="modal-backdrop bg-slate-900/40 backdrop-blur-sm" wire:click="closeModal"></div>
    </div>
</div>