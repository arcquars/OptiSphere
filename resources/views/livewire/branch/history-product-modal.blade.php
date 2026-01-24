<div>
    @if ($showForm)
        <div class="modal modal-open modal-top">
            <div class="modal-box border-t-4 border-success">
                <h3 class="font-bold text-xl text-success">Historial de producto para la Sucursal: {{ $this->branch->name }}</h3>
                <hr class="my-2">
                <p class="mx-2"><b>Nombre: </b> <span>{{ $this->product->name }}</span> | <b>Codigo: </b> <span>{{ $this->product->code }}</span></p>
                <div class="overflow-x-auto">
                    <table class="table table-xs">
                        <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>Movimiento</th>
                            <th>Saldo Anterior</th>
                            <th>Saldo Nuevo</th>
                            <th>Cant.</th>
                            <th>Usuario</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $movement)
                                <tr>
                                    <th>{{ $movement->created_at }}</th>
                                    <td>{{ $movement->from_location_name }}</td>
                                    <td>{{ $movement->to_location_name }}</td>
                                    <td>{{ $movement->type }}</td>
                                    <td>{{ $movement->old_quantity }}</td>
                                    <td>{{ $movement->new_quantity }}</td>
                                    <td>{{ $movement->difference }}</td>
                                    <td>{{ $movement->user->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{ $movements->links() }}
                <hr class="my-2">
                <div class="modal-action">
                    {{-- <button wire:click="revertSale" class="btn btn-success btn-outline">Imprimir</button> --}}
                    <button wire:click="closeModal" class="btn">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>
