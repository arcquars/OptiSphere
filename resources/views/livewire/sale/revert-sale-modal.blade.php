<div>
    @if ($showForm)
        <div class="modal modal-open">
            <div class="modal-box border-t-4 border-success">
                <h3 class="font-bold text-lg">Eliminar Venta</h3>
                <p class="py-4">Estás a punto de Revertir la anulacion de la factura. ¿Deseas continuar?</p>
                
                <div class="modal-action">
                    <button wire:click="revertSale" class="btn btn-success btn-outline">Revertir anulacion factura</button>
                    <button wire:click="closeModal" class="btn">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>
