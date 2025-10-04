<div
    x-data="{
        open: false,
        customerSelect: null,
        changeUploadCustomerCart(id, name, nit){
            // alert(JSON.stringify());
            this.customerSelect = name + '(' + nit + ')';
            $dispatch('customer-updated', {id});
        }

    }"
    class="flex flex-col relative"
>
    <div class="join w-full flex-grow">
        <input
            wire:model.live.debounce.300ms="customerSearch"
            x-model="customerSelect"
            class="input input-bordered join-item w-full focus:outline-none"
            placeholder="Buscar cliente/NIT"
            @focus="open = true"
{{--            @blur="open = false"--}}
            @click.outside="open = false"
        />
        <button
            class="btn join-item btn-accent"
            onclick="document.getElementById('modal_cliente').showModal()"
        >
            <i class="fa-solid fa-user-plus"></i>
        </button>
    </div>

    {{-- El dropdown con los resultados --}}
    @if(!empty($searchResults))
        <ul
            x-show="open"
            x-transition
            tabindex="0"
            class="absolute top-full left-0 z-50 menu p-2 shadow bg-base-100 rounded-box w-full"
        >
            @forelse($searchResults as $result)
                <li>
                    <a
                        @click.prevent="changeUploadCustomerCart({{ $result->id }}, '{{ $result->name }}', '{{ $result->nit }}')"
                    >
                        <div class="flex items-center gap-3">
                            <div>
                                <div class="font-bold">{{ $result->name }} <small>(NIT: {{ $result->nit }})</small></div>
                            </div>
                        </div>
                    </a>

                </li>
            @empty
                <li><a>No se encontraron resultados.</a></li>
            @endforelse
        </ul>
    @endif


<!-- Modal para Registrar Cliente -->
    <dialog id="modal_cliente" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Registrar Nuevo Cliente</h3>
            <form wire:submit.prevent="saveCustomer">
                <div class="py-4 space-y-4">
                    <input type="text" placeholder="Nombre completo" wire:model="newCustomerName" class="input input-bordered w-full" />
                    @error('newCustomerName') <span class="text-error text-sm">{{ $message }}</span> @enderror

                    <input type="text" placeholder="NIT / Cédula" wire:model="newCustomerNit" class="input input-bordered w-full" />
                    @error('newCustomerNit') <span class="text-error text-sm">{{ $message }}</span> @enderror

                    <input type="email" placeholder="Email (opcional)" wire:model="newCustomerEmail" class="input input-bordered w-full" />
                    @error('newCustomerEmail') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn" onclick="document.getElementById('modal_cliente').close()">Cerrar</button>
                </div>
            </form>
        </div>
    </dialog>

    @push('scripts')
        <script>
            document.addEventListener('livewire:load', function () {
                // Escucha el evento del backend para cerrar el modal
                Livewire.on('close-customer-modal', () => {
                    document.getElementById('modal_cliente').close();
                });
            });
        </script>
    @endpush
</div>

