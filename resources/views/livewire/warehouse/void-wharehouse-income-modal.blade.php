<div>
    <div class="modal {{ $isOpen ? 'modal-open' : '' }}" role="dialog">
        <div class="modal-box max-w-2xl border-t-4 border-red-700 shadow-2xl">
            <h3>Confirme que quiere anular TODA la entrega</h3>
            <input 
                type="hidden" 
                wire:model.prevent="warehouseInvoiceId"
            />
            @error('warehouseInvoiceId')
                <p class="label-text-alt text-error mt-1">{{ $message }}</p>
            @enderror
            <div class="modal-action">
                <button 
                    type="button"
                    wire:click="voidRegister" 
                    class="btn btn-error btn-sm btn-outline"
                    wire:loading.attr="disabled"
                >Aceptar</button>
                <button wire:click="closeVoidWherhouseIncomeModal" class="btn btn-sm">Cerrar</button>
            </div>
        </div>


        {{-- Overlay para cerrar al hacer clic fuera --}}
        <div class="modal-backdrop bg-slate-900/40 backdrop-blur-sm" wire:click="closeModal"></div>
    </div>
</div>