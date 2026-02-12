<div 
    class="flex flex-col items-center justify-center p-4 bg-white rounded-xl border border-gray-200 shadow-inner mb-4"
    @if($shouldPoll) wire:poll.10s="checkStatus12" @endif 
>
    @if($shouldPoll)
    <div class="flex items-center gap-2 mt-3 text-xs text-gray-400 font-medium">
        <span class="loading loading-ring loading-xs text-primary"></span>
        Esperando confirmación de pago...
    </div>
    @else
        <div class="flex items-center gap-2 mt-3 text-sm text-success font-bold">
            <i class="fa-solid fa-circle-check"></i>
            ¡Pago confirmado! Procesando...
        </div>
    @endif
</div>
