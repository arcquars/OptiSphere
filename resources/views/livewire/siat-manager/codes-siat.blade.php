<div class="grid grid-cols-2 gap-1">
    <div>
        <h2 class="text-xl text-primary font-bold">CUIS</h2>
        <p class="pl-2">{{ $this->siatSucursalPuntoVenta?->cuis?? '--' }}</p>
        <p class="pl-2"><b>Fecha de expiración:</b> {{ $this->siatSucursalPuntoVenta?->cuis_date?? '--' }}</p>
        <br>
        <button class="btn btn-sm btn-block btn-primary" type="button" wire:click="getCuis">Obtener CUIS</button>
    </div>
    <div>
        <h2 class="text-xl text-primary font-bold">CUFD</h2>
        <div class="pl-2 overflow-y-auto">
            <p>df87s97fds987f9sd7fd8s7f98f9ds7f8d7f8df7ds98f7ds98f7d89f7d98f7ds89f7ds87fsd9898dsd789ds78d9sa78</p>
        </div>
        <p class="pl-2"><b>Código de control:</b> 4564WEE5554</p>
        <p class="pl-2"><b>Fecha de expiración:</b> 2025/15/12</p>
        <button class="btn btn-sm btn-block btn-primary">Obtener CUFD</button>
    </div>
</div>
