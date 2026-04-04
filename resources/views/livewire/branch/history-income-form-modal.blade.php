<div>
    @if ($showHistoryIncomeOpen)
        <div class="modal modal-open">
            <div class="modal-box max-w-4xl">
                <form wire:submit.prevent="load">
                    <h3 class="font-bold text-lg mb-4">Historial de entregas</h3>

                    {{-- Formulario Filament --}}
                    
                        {{ $this->form }}
                    

                    {{-- Renderiza JS/Alpine necesario para los componentes Filament --}}
                    <x-filament-actions::modals />

                    <div class="modal-action mt-4">
                        <button type="submit" class="btn btn-sm btn-primary">
                            Cargar
                        </button>
                        <button type="button" wire:click="toggleHisIncomeOpenForm" class="btn btn-sm">
                            Cerrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>