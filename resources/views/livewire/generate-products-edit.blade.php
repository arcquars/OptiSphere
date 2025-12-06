<div>
    <form wire:submit.prevent="update">
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-1">
                <fieldset class="fieldset">
                    <label class="label label-azteris">Codigo Base</label>
                    {{-- INPUT DESHABILITADO --}}
                    <input 
                        type="text" 
                        class="w-full input input-disabled select-secondary bg-gray-200 cursor-not-allowed" 
                        wire:model="baseCode" 
                        disabled 
                    />
                </fieldset>
            </div>
            <div class="col-span-2">
                <fieldset class="fieldset">
                    <label class="label label-azteris">Proveedor</label>
                    <select class="w-full select @error('supplier') select-secondary @enderror" wire:model="supplier">
                        <option value="">Seleccione ...</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier')
                    <div role="alert" class="alert alert-error alert-soft">
                        <span>{{ $message }}</span>
                    </div>
                    @enderror
                </fieldset>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-1">
                <fieldset class="fieldset">
                    <label class="label label-azteris">Precio cliente normal</label>
                    <input type="number" class="w-full input @error('priceNormal') select-secondary @enderror"
                           wire:model="priceNormal" step="0.01" />

                    @error('priceNormal')
                    <div role="alert" class="alert alert-error alert-soft">
                        <span>{{ $message }}</span>
                    </div>
                    @enderror
                </fieldset>
            </div>
            <div class="col-span-1">
                <fieldset class="fieldset">
                    <label class="label">Precio cliente especial</label>
                    <input type="number" class="w-full input @error('priceSpecial') select-secondary @enderror"
                           wire:model="priceSpecial" step="0.01" />
                    @error('priceSpecial')
                    <div role="alert" class="alert alert-error alert-soft">
                        <span>{{ $message }}</span>
                    </div>
                    @enderror
                </fieldset>
            </div>
            <div class="col-span-1">
                <fieldset class="fieldset">
                    <label class="label">Precio cliente mayorista</label>
                    <input type="number" class="w-full input @error('priceWholesale') select-secondary @enderror"
                           wire:model="priceWholesale" step="0.01" />
                    @error('priceWholesale')
                    <div role="alert" class="alert alert-error alert-soft">
                        <span>{{ $message }}</span>
                    </div>
                    @enderror
                </fieldset>
            </div>
        </div>

        {{-- Formulario SIAT de Filament --}}
        <div class="mt-4">
            {{ $this->form }}
        </div>

        <button class="btn btn-warning mt-4" type="submit">Actualizar</button>
    </form>
</div>