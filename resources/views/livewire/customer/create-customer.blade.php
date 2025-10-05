<div>
    @if ($showForm)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Crear Cliente</h3>
                <form wire:submit="create">
                    {{ $this->form }}

                    <div class="modal-action">
                        <button type="submit" class="btn btn-sm btn-success">Crear
                        </button>
                        <button wire:click="toggleForm" class="btn btn-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
