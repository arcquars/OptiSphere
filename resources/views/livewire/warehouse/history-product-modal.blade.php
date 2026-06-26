<?php
use App\Models\Warehouse;
?>
<div>
    @if ($showForm)
        <div class="modal modal-open modal-top">
            <div class="modal-box border-t-4 border-success">
                <h3 class="font-bold text-xl text-success">Historial de producto para {{ $this->warehouse->name }}</h3>
                <hr class="my-2">
                <p class="mx-2"><b>Nombre: </b> <span>{{ $this->product->name }}</span></p>
                <div class="overflow-x-auto">
                    <table class="table table-xs">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>N.</th>
                            <th>Fecha</th>
                            <th>Movimiento</th>
                            <th>Q. anterior</th>
                            <th>Q. nueva</th>
                            <th>Diferencia</th>
                            <th>Sucursal</th>
                            <th>Usuario</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $i => $movement)
                                <tr>
                                    <th>{{ $i }}</th>
                                    <td>{{ $movement->id?? "--" }}</td>
                                    <td>{{ $movement->date }}</td>
                                    <td>{{ $movement->movement_label }}</td>
                                    <td>{{ $movement->old_quantity }}</td>
                                    <td>{{ $movement->new_quantity }}</td>
                                    <td>{{ $movement->difference }}</td>
                                    <td>{{ $movement->branch_name }}</td>
                                    <td>{{ $movement->user_name?? "--" }}</td>
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
