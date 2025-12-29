<div>
    @if ($showForm)
        <div class="modal modal-open">
            <div class="modal-box border-t-4 border-error">
                <h3 class="font-bold text-lg">Eliminar Venta</h3>
                <p class="py-4">Estás a punto de eliminar @if($isSiat) y anular la factura @endif esta venta. ¿Deseas continuar?</p>
                @if($isSiat)
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Motivo anulacion <span class="text-error">*</span></legend>
                    <select class="select w-full" wire:model.defer="motivo">
                        <option value="" selected>-- Motivo anulacion --</option>
                        @foreach ($motivosAnulacion as $motivo)
                            <option value="{{ $motivo['codigoClasificador'] }}">{{ $motivo['descripcion'] }}</option>
                        @endforeach
                    </select>
                    @error('motivo') 
                    <div class="label text-error">{{ $message }}</div>
                    @enderror
                </fieldset>
                @endif

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Nota</legend>
                    <textarea class="textarea textarea-bordered w-full" wire:model="voidReason" placeholder="Motivo (opcional)"></textarea>
                    <!-- <div class="label">Optional</div> -->
                </fieldset>
                
                <div class="modal-action">
                    @if($this->deleteSale)
                    <button wire:click="voidSale" class="btn btn-error btn-outline">Eliminar venta / Anular factura</button>
                    @else
                    <button wire:click="voidSale" class="btn btn-error btn-outline">Anular factura</button>
                    @endif
                    <button wire:click="closeModal" class="btn">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>
