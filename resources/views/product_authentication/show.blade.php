@extends('layouts.app')

@section('content')
<div class="container text-center" style="margin-top: 50px;">
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title">
                <span class="glyphicon glyphicon-ok-sign"></span> ¡Producto Auténtico Verificado!
            </h3>
        </div>
        <div class="panel-body">
            <!-- Mostramos información de la tabla product_authentication -->
            <h2>{{ $auth->nombre_comercial }}</h2>
            <p><strong>Número de Lote:</strong> {{ $auth->lote }}</p>
            <p><strong>Fecha de Fabricación:</strong> {{ $auth->fecha_fabricacion }}</p>

            <hr>

            <!-- Si configuraste la relación con tu tabla "products" principal -->
            @if($auth->product)
                <h3>Detalles Técnicos:</h3>
                <p>{{ $auth->product->descripcion }}</p>
                <p><strong>Garantía:</strong> {{ $auth->product->garantia }}</p>
            @endif
        </div>
    </div>
</div>
@endsection