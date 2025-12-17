<div wire:poll.5s="checkStatus" class="flex items-center justify-end">
    
    {{-- CASO 1: HAY UN EVENTO DE CONTINGENCIA ACTIVO --}}
    @if($hasActiveContingency)
        <div class="flex items-center gap-2 bg-warning/20 border border-warning text-warning-content px-3 py-1 rounded-lg animate-pulse">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div class="flex flex-col leading-none">
                <span class="text-xs font-bold uppercase">Contingencia Activa</span>
                <span class="text-[10px]">{{ $eventDescription }}</span>
                <span class="text-[10px] mb-1">ID: {{ $eventId }} - Inicio: {{ $eventStartTime }}</span>
                @if($isOnline)
                <button wire:click="closeContingencyEvent" class="btn btn-xs btn-info">Cerrar Evento</button>
                @endif
            </div>
        </div>
    @endif
    {{-- CASO 2: CONEXIÓN EXITOSA (ONLINE) --}}
    @if($isOnline)
        <!-- <div class="badge badge-success gap-2 p-3 font-semibold text-white shadow-sm" title="Conexión con SIAT estable">
            <i class="fa-solid fa-wifi text-xs"></i>
            SIAT EN LÍNEA
        </div> -->
        <div class="dropdown dropdown-bottom dropdown-end">
            <div tabindex="0" role="button" class="btn btn-sm btn-success m-1" @if($hasActiveContingency) disabled @endif>SIAT EN LÍNEA <i class="fa-regular fa-square-caret-down"></i></div>
            <ul tabindex="-1" class="dropdown-content menu bg-base-200 rounded-box z-1 w-70 p-2 shadow-sm">
                @foreach ($this->eventosSiat as $key => $event)
                    <li class="text-sm"><a wire:click="createEvent({{$key}})">{{ $key }}.- {{ $event }}</a></li>
                @endforeach
            </ul>
        </div>

    {{-- CASO 3: SIN CONEXIÓN (OFFLINE) --}}
    @else
        <div class="flex items-center gap-2">
            <button wire:click="openContingencyModal" 
                    class="btn btn-error btn-sm btn-outline gap-2 animate-bounce">
                <i class="fa-solid fa-wifi-slash"></i>
                SIAT OFFLINE
                <span class="badge badge-error text-white text-xs">Crear Evento</span>
            </button>
        </div>
    @endif

    {{-- MODAL DE CREACIÓN DE EVENTO --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-base-100 rounded-box shadow-xl w-full max-w-md p-6 transform transition-all">
                <h3 class="font-bold text-lg text-error flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    Iniciar Contingencia
                </h3>
                <p class="py-4 text-sm text-base-content/70">
                    Se ha detectado una falla en la comunicación con el SIAT. 
                    ¿Desea iniciar un evento de contingencia manual (Offline)?
                </p>

                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">Motivo de Contingencia</span>
                    </label>
                    <select wire:model="reason" class="select select-bordered select-error w-full">
                        <option value="">Seleccione un motivo...</option>
                        <option value="1">Corte de suministro de energía eléctrica</option>
                        <option value="2">Corte de servicio de Internet</option>
                        <option value="3">Inaccesibilidad al Servicio Web del SIN</option>
                        <option value="4">Ingreso a zonas sin Internet</option>
                        <option value="5">Venta en lugares sin Internet</option>
                        <option value="6">Virus informático o falla de software</option>
                        <option value="7">Cambio de infraestructura de sistema</option>
                    </select>
                </div>

                <div class="modal-action flex justify-end gap-2 mt-6">
                    <button wire:click="$set('showModal', false)" class="btn btn-ghost">Cancelar</button>
                    <button wire:click="createContingencyEvent" class="btn btn-error text-white">
                        <i class="fa-solid fa-save"></i> Iniciar Evento
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>