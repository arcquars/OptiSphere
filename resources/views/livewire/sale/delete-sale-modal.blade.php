<div>
    @if ($showForm)
        <div class="modal modal-open">
            <div class="modal-box border-t-4 border-error">
                <h3 class="font-bold text-lg">Eliminar Venta</h3>
                <p class="py-4">Estás a punto de eliminar esta venta. ¿Deseas continuar?</p>
                <textarea class="textarea textarea-bordered w-full" wire:model="voidReason" placeholder="Motivo (opcional)"></textarea>
                <div class="modal-action">
                    <button wire:click="voidSale" class="btn btn-error btn-outline">Eliminar</button>
                    <button wire:click="closeModal" class="btn">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>
