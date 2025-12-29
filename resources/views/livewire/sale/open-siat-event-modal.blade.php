<div>
    @if ($showForm)
        <div class="modal modal-open">
            <div class="modal-box border-t-4 border-info">
                <h3 class="font-bold text-info text-base">Abrir Evento 
                    <small>(Sucursal: {{ $this->branch->amyrConnectionBranch->sucursal }} | Punto de venta: {{ $this->branch->amyrConnectionBranch->point_sale }})</small>
                </h3>
                <p class="text-sm mb-2">{{ $this->codeEvent }}. {{ $this->nameEvent }}</p>

                <div class="grid grid-cols-2 gap-2">
                    <fieldset class="fieldset col-span-2">
                        <label class="label">CUFD<span class="text-error">*</span></label>
                        <select wire:model.defer="cufd" class="select select-sm w-full">
                            <option selected>Seleccione ...</option>
                            @foreach ($this->cufds as $cufd)
                                <option value="{{ $cufd['codigo'] }}">{{ $cufd['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="text-error">@error('cufd') {{ $message }} @enderror</div>
                    </fieldset>
                    <fieldset class="fieldset">
                        <label class="label">Fecha / Hora inicio<span class="text-error">*</span></label>
                        <input wire:model.defer="dateIni" type="datetime-local" class="input input-sm">
                        <div class="text-error">@error('dateIni') {{ $message }} @enderror</div>
                    </fieldset>
                    <fieldset class="fieldset">
                        <label class="label">Fecha / Hora fin<span class="text-error">*</span></label>
                        <input wire:model.defer="dateEnd" type="datetime-local" class="input input-sm">
                        <div class="text-error">@error('dateEnd') {{ $message }} @enderror</div>
                    </fieldset>
                </div>
                

                <div class="modal-action">
                    <button wire:click="saveEvent" class="btn btn-info btn-sm btn-outline">Abrir evento</button>
                    <button wire:click="closeModal" class="btn btn-sm">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>