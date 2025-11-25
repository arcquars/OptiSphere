<div>
    @switch($active)
        @case(2)
            <livewire:siat-manager.activities-siat :branchId="$branchId" />
            @break

        @case(3)
            @livewire('siat-manager.activities-sector-siat', ['branchId' => $branchId])
            @break
        @case(4)
            @livewire('siat-manager.leyendas-siat', ['branchId' => $branchId])
            @break
        @case(5)
            @livewire('siat-manager.producto-servicio-siat', ['branchId' => $branchId])
            @break
        @case(6)
            @livewire('siat-manager.eventos-siat', ['branchId' => $branchId])
            @break
        @case(7)
            @livewire('siat-manager.motivo-anulacion-siat', ['branchId' => $branchId])
            @break
        @case(8)
            @livewire('siat-manager.documentos-identidad-siat', ['branchId' => $branchId])
            @break
        @case(9)
            @livewire('siat-manager.tipo-documentos-sector-siat', ['branchId' => $branchId])
            @break
        @default
            <livewire:siat-manager.codes-siat :branchId="$branchId" />

    @endswitch

    
</div>
