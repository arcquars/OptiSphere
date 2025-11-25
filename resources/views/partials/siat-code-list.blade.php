<table class="table table-zebra">
    <!-- head -->
    <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
        </tr>
    </thead>
    <tbody>
        @if($items)
            @foreach ($items as $index => $act)
                <tr>
                    <td>{{ $act->codigo_clasificador }}</td>
                    <td>{{ $act->descripcion }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>