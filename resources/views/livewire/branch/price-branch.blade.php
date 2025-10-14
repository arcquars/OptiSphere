<div>
    @if (session('success'))
        <div role="alert" class="alert alert-success mt-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($showForm)
        <div class="modal modal-open">
            <div class="modal-box w-11/12 max-w-5xl">
                <form wire:submit="savePrices">
                <h3 class="font-bold text-lg mb-4">Precios de producto</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-neutral-400 mb-1">Nombre:</p>
                        <p class="text-sm">{{ ($product)? $product->name : 'x' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-400 mb-1">Código:</p>
                        <p class="text-sm">{{ ($product)? $product->code : 'x' }}</p>
                    </div>
                </div>
                    <hr class="my-2">
                    <p class="text-xs font-semibold">Precio Almacen:</p>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-neutral-400 mb-1">Normal:
                                <span class="text-base text-stone-950 pl-2">{{ ($product)? $product->getPriceByType(null, \App\Models\Price::TYPE_NORMAL) : '0' }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-neutral-400 mb-1">Especial:
                                <span class="text-base text-stone-950 pl-2">
                                    {{ ($product)? $product->getPriceByType(null, \App\Models\Price::TYPE_ESPECIAL) : '0' }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-neutral-400 mb-1">Mayorista:
                                <span class="text-base text-stone-950 pl-2">
                                    {{ ($product)? $product->getPriceByType(null, \App\Models\Price::TYPE_MAYORISTA) : '0' }}
                                </span>
                            </p>
                        </div>
                    </div>
                <hr class="border-amber-600 my-3">
                <div class="grid grid-cols-3 gap-4">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Precio normal</legend>
                        <input type="number" class="input input-sm focus:outline-none {{ $errors->has('priceNormal') ? 'input-error' : '' }}"
                               wire:model.defer="priceNormal"
                               min="0" max="150000"
                               placeholder="Ej: 10.55"
                               step="0.01"
                        />
                        <div>
                            @error('priceNormal')
                            <p class="label text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Precio especial</legend>
                        <input type="number" class="input input-sm focus:outline-none {{ $errors->has('priceEspecial') ? 'input-error' : '' }}"
                               wire:model.defer="priceEspecial"
                               min="0" max="150000"
                               placeholder="Ej: 10.55"
                               step="0.01"
                        />
                        <div>
                            @error('priceEspecial')
                            <p class="label text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Precio por mayor</legend>
                        <input type="number" class="input input-sm focus:outline-none {{ $errors->has('priceMayor') ? 'input-error' : '' }}"
                               wire:model.defer="priceMayor"
                               min="0" max="150000"
                               placeholder="Ej: 10.55"
                               step="0.01"
                        />
                        <div>
                            @error('priceMayor')
                            <p class="label text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </fieldset>
                </div>



                <div class="modal-action">
                    <button type="submit" class="btn btn-sm btn-success">Guardar
                    </button>
                    <button wire:click="toggleForm" class="btn btn-sm">Cancelar</button>
                </div>
                </form>
            </div>
        </div>
    @endif
</div>
