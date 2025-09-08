<div>
    <div x-data='matrixOptical({
{{--            initialStocks: <?php if(isset($initialStocks) && count($initialStocks) > 0): ?> @json($initialStocks) <?php else: ?>{}<?php endif; ?>,--}}
            initialStocks: $wire.matrix,
            test: $wire.action
            })'
         x-init="console.log('cccc 1')"

         @clear-markedcells.window="
        // Actualiza la variable 'stock' con el dato recibido del evento
        markedCells = [];
     "
    >
        <!-- Rectángulo de selección -->
        <div
            x-ref="selectionBox"
            class="absolute border-2 border-blue-400 bg-blue-200/30 pointer-events-none z-50"
            :style="selectionBoxStyle()"
        ></div>
        sss
        <div x-text="$wire.matrix"></div>
        qqq
        <div x-text="$wire.action"></div>
        www
        <div x-text="test"></div>
        qqq
        <div x-text="initialStocks"></div>
        www

    </div>

</div>
