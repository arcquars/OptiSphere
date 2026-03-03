<div>
    {{-- 
        Usamos la clase 'modal-open' de daisyUI basada en la variable $isOpen de Livewire.
        'backdrop-blur' le da un toque premium al fondo.
    --}}
    <div class="modal {{ $isOpen ? 'modal-open' : '' }}" role="dialog">
        <div class="modal-box max-w-2xl border-t-4 border-primary shadow-2xl">
            
            @if($product)
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="font-bold text-2xl text-base-content">{{ $product->name }}</h3>
                    </div>
                    <button wire:click="closeModal" class="btn btn-sm btn-circle btn-ghost">✕</button>
                </div>

                <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100">
                <table class="table">
                    <!-- head -->
                    <thead>
                    <tr>
                        <th></th>
                        <th>Sucursal</th>
                        <th>P. Normal</th>
                        <th>P. Especial</th>
                        <th>P. Mayorista</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->branches as $i => $branch)
                        <tr>
                            <th>{{ $i }}</th>
                            <td>{{ $branch->name }}</td>
                            @php
                            $priceNormal = "";
                            $priceEspecial = "";
                            $priceMayorista = "";
                            @endphp
                            @foreach ($product->prices as $i => $price)
                                @if($branch->id == $price->branch_id)
                                    @if(strcmp($price->type, App\Models\Price::TYPE_NORMAL) == 0)
                                        @php
                                         $priceNormal = $price->price   
                                        @endphp
                                    @elseif(strcmp($price->type, App\Models\Price::TYPE_ESPECIAL) == 0)
                                        @php
                                         $priceEspecial = $price->price   
                                        @endphp
                                    @elseif(strcmp($price->type, App\Models\Price::TYPE_MAYORISTA) == 0)
                                        @php
                                         $priceMayorista = $price->price   
                                        @endphp
                                    @else
                                        @php
                                         $priceMayorista = "--"   
                                        @endphp
                                    @endif
                                @endif
                            @endforeach   
                            <td>{{ $priceNormal }}</td> 
                            <td>{{ $priceEspecial }}</td>
                            <td>{{ $priceMayorista }}</td>  
                        </tr>
                        @endforeach
                        
                         
                    </tbody>
                </table>
                
            @else
                <div class="flex flex-col items-center py-10">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                    <p class="mt-4 text-sm opacity-50">Cargando información del producto...</p>
                </div>
            @endif
        </div>

        {{-- Overlay para cerrar al hacer clic fuera --}}
        <div class="modal-backdrop bg-slate-900/40 backdrop-blur-sm" wire:click="closeModal"></div>
    </div>
</div>