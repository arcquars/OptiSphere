<!-- DIV 1: Ancho Fijo (ej. 12rem / 192px) -->
<div 
    x-data="{
        active: $wire.entangle('siatActive'),
        reloadActiveSiat(active){
            this.active = active;
            // llamada a emit switch-reload con parametro
            this.$dispatch('switch-reload', { active });
        }
    }"
    class="w-64"
>
    <ul class="menu bg-base-200 rounded-box w-max">
        <li><a x-on:click="reloadActiveSiat(1)" :class="active===1? 'menu-active' : ''">Códigos</a></li>
        <li><a x-on:click="reloadActiveSiat(2)" :class="active===2? 'menu-active' : ''">Actividades</a></li>
        <li>
            <a x-on:click="reloadActiveSiat(3)" :class="active===3? 'menu-active' : ''">
                Actividades documento sector
            </a>
        </li>
        <li><a x-on:click="reloadActiveSiat(4)" :class="active===4? 'menu-active' : ''">Leyendas factura</a></li>
        <li><a x-on:click="reloadActiveSiat(5)" :class="active===5? 'menu-active' : ''">Productos / Servicios</a></li>
        <li><a x-on:click="reloadActiveSiat(6)" :class="active===6? 'menu-active' : ''">Eventos Significativos</a></li>
        <li><a x-on:click="reloadActiveSiat(7)" :class="active===7? 'menu-active' : ''">Motivos de anulación</a></li>
        <li><a x-on:click="reloadActiveSiat(8)" :class="active===8? 'menu-active' : ''">Tipos de documentos de identidad</a></li>
        <li><a x-on:click="reloadActiveSiat(9)" :class="active===9? 'menu-active' : ''">Tipos de documentos de sector</a></li>
        <li><a x-on:click="reloadActiveSiat(10)" :class="active===10? 'menu-active' : ''">Tipos de emisiones</a></li>
        <li><a x-on:click="reloadActiveSiat(11)" :class="active===11? 'menu-active' : ''">Tipos Metodo de pagos</a></li>
        <li><a x-on:click="reloadActiveSiat(12)" :class="active===12? 'menu-active' : ''">Tipo Monedas</a></li>
        <li><a x-on:click="reloadActiveSiat(13)" :class="active===13? 'menu-active' : ''">Tipo Puntos de venta</a></li>
        <li><a x-on:click="reloadActiveSiat(14)" :class="active===14? 'menu-active' : ''">Tipo Factura</a></li>
        <li><a x-on:click="reloadActiveSiat(15)" :class="active===15? 'menu-active' : ''">Unidad de Medida</a></li>
    </ul>
</div>