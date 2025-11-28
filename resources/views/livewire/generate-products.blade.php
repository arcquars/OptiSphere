<div>
    <form wire:submit.prevent="generateProducts">
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-1">
                <fieldset class="fieldset">
                    <label class="label label-azteris">Codigo Base</label>
                    <input type="text" class="w-full input @error('baseCode') select-secondary @enderror" wire:model="baseCode" />
                    @error('baseCode')
                    <div role="alert" class="alert alert-error alert-soft">
                        <span>{{ $message }}</span>
                    </div>
                    @enderror
                </fieldset>
            </div>
            <div class="col-span-2">
                <fieldset class="fieldset">
                    <label class="label label-azteris">Proveedor</label>
                    <select class="w-full select @error('supplier') select-secondary @enderror" wire:model="supplier">
                        <option value="">Seleccione ...</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
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

        <div>
            <p class="text-black">Se generara los rangos:</p>
            <p class="text-success pl-4"><b>Cilindro:</b> 0.00 - 6.00</p>
            <p class="text-blue-800 pl-4"><b>Esferico positivo:</b> 0.00 - 6.00</p>
            <p class="text-error pl-4"><b>Esferico negativo:</b> 0.00 - 6.00</p>
        </div>

        <div class="mt-4">
            {{ $this->form }}
        </div>

        <button class="btn btn-primary mt-4" type="submit">Crear</button>
    </form>
</div>