<div class="grid grid-cols-2 gap-1">
    <div>
        <h2 class="text-xl text-primary font-bold">CUIS</h2>
        <p class="pl-2">{{ $this->siatSucursalPuntoVenta?->cuis ?? '--' }}</p>
        <p class="pl-2"><b>Fecha de expiración:</b> {{ $this->siatSucursalPuntoVenta?->cuis_date ?? '--' }}</p>
        <br>
        <button 
            class="btn btn-sm btn-block btn-primary" 
            type="button" 
            wire:click="getCuis"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="getCuis">
                Obtener CUIS
            </span>
            <span wire:loading wire:target="getCuis">
                <i class="fa-solid fa-spinner fa-spin-pulse" role="status" aria-hidden="true"></i>
                Obteniendo CUIS...
            </span>
        </button>
    </div>
    <div>
        <h2 class="text-xl text-primary font-bold">CUFD</h2>
        <div class="pl-2 overflow-y-auto">
            <p>{{ $this->cufd?->codigo ?? '--' }}</p>
        </div>
        <p class="pl-2"><b>Código de control:</b> {{ $this->cufd?->codigo_control ?? '--' }}</p>
        <p class="pl-2"><b>Fecha de expiración:</b> {{ $this->cufd?->fecha_vigencia ?? '--' }}</p>
        <button type="button" 
            wire:click="getCufd" 
            wire:loading.attr="disabled"
            class="btn btn-sm btn-block btn-primary">
            <!-- Muestra 'Obtener CUFD' normalmente -->
            <span wire:loading.remove wire:target="getCufd">
                Obtener CUFD
            </span>

            <!-- Muestra 'Obteniendo CUFD...' SOLO mientras 'getCufd' se está ejecutando -->
            <span wire:loading wire:target="getCufd">
                <i class="fa-solid fa-spinner fa-spin-pulse" role="status" aria-hidden="true"></i>
                Obteniendo CUFD...
            </span>
        </button>
    </div>
</div>