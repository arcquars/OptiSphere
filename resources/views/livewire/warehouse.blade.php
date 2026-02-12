<div x-data='{
                    markedCells: [],
                    markedAmountCells: [],
                    amount: "",
                    selecting: false,
                    startX: 0,
                    startY: 0,
                    currentX: 0,
                    currentY: 0,
                    cells: {},
                    branchId: null,
                    priceType: null,
                    messageError: "",
                    disableSavePrices: false,
                    sendBranch: false,
                    init() {
                        console.log(`ccc2`, this.cells);
                    },
                toggleCell(id) {
                    if (this.markedCells.includes(id)) {
                        this.markedCells = this.markedCells.filter(item => item !== id)
                        this.markedAmountCells = this.markedAmountCells.filter(item => item.id !== id)

                        if(this.cells[id]){
                            // this.cells[id] = "-"
                            this.cells[id] = this.getAmountInTableCell(id);
                        }
                    } else {
                        this.markedCells.push(id)
                    }
                    this.setEnableSavePrices();
                },
                changeSelected(action) {
                    this.messageError = "";
                    if (this.amount === ""){
                        this.messageError = "Cantidad es requerido";
                        return  // no hacer nada si está vacío
                    }

                    this.markedCells.forEach(id => {
                        if(action === "entregas"){
                            console.log(action + " | " + this.cells[id] + " | " + this.amount);
                            if (this.markedAmountCells.some(item => item.id === id)) {
                                    this.markedAmountCells = this.markedAmountCells.filter(item => item.id !== id)
                            }
                            if(this.validateAmountInTable(id, this.amount)){
                                this.cells[id] = this.amount
                                this.markedAmountCells.push({id, amount: this.amount, state: "success"});
                            } else {
                                this.markedAmountCells.push({id, amount: "-", state: "danger"});
                                this.messageError = "Existen productos que su cantidad en inventario es menor al asignado";
                            }

                        } else {
                            this.cells[id] = this.amount
                            if (this.markedAmountCells.some(item => item.id === id)) {
                                this.markedAmountCells = this.markedAmountCells.filter(item => item.id !== id)
                            }
                            this.markedAmountCells.push({id, amount: this.amount, state: "success"});
                        }
                    })
                    this.markedCells = [];
                    this.setEnableSavePrices();
                },

                startSelection(e) {
                    this.selecting = true
                    this.startX = e.pageX
                    this.startY = e.pageY
                    this.currentX = e.pageX
                    this.currentY = e.pageY
                },
                updateSelection(e) {
                    if (!this.selecting) return
                    this.currentX = e.pageX
                    this.currentY = e.pageY

                    let rect = this.$refs.selectionBox.getBoundingClientRect()

                    this.$refs.table.querySelectorAll("[data-cell-id]").forEach(cell => {
                        let cellRect = cell.getBoundingClientRect()
                        if (!(rect.right < cellRect.left ||
                              rect.left > cellRect.right ||
                              rect.bottom < cellRect.top ||
                              rect.top > cellRect.bottom)) {
                            let id = parseInt(cell.getAttribute("data-cell-id"))
                            if (!this.markedCells.includes(id)) {
                                this.markedCells.push(id)
                            }
                        }
                    })
                },
                endSelection() {
                    this.selecting = false
                },
                selectionBoxStyle() {
                    if (!this.selecting) return "display: none;"
                    let x = Math.min(this.startX, this.currentX)
                    let y = Math.min(this.startY, this.currentY)
                    let w = Math.abs(this.startX - this.currentX)
                    let h = Math.abs(this.startY - this.currentY)
                    return `display: block; left:${x}px; top:${y}px; width:${w}px; height:${h}px;`
                },
                uploadText(id, text) {
                    return this.cells[id]? this.cells[id] : text;
                },
                clearCells(){
                    console.log("ccc 234");
                    this.markedCells = [];
                    this.markedAmountCells = [];
                    this.cells = {};
                    this.amount = "";
                    this.messageError = "";
                },
                validateAmountInTable(id, amount){
                    let cellAmount = null;
                    const cells = this.$refs.table.querySelectorAll("[data-cell-id]");
                    for (const cell of cells) {
                        const cellId = parseInt(cell.getAttribute("data-cell-id"));

                        if (cellId === id) {
                            cellAmount = parseInt(cell.getAttribute("data-cell-amount"));
                            break;
                        }
                    }

                    if(cellAmount !== null && cellAmount >= amount){
                        return true;
                    }

                    return false;
                },
                getAmountInTableCell(id){
                    let cellAmount = null;
                    const cells = this.$refs.table.querySelectorAll("[data-cell-id]");
                    for (const cell of cells) {
                        const cellId = parseInt(cell.getAttribute("data-cell-id"));
                        if (cellId === id) {
                            cellAmount = parseInt(cell.getAttribute("data-cell-amount"));
                            break;
                        }
                    }
                    if(cellAmount !== null){
                        return cellAmount;
                    }

                    return "";
                },
                async setPositionYMatrix(){
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    const element = document.getElementById("t-matrix");
                    if (element) {
                        element.scrollIntoView({ behavior: "smooth" });
                    }
                },
                setEnableSavePrices() {
                    console.log("Entro a setEnableSavePrices: " + this.priceType);
                    if(this.markedAmountCells.length !== 0 &&
{{--                        markedAmountCells.some(item => item.state === "danger") ||--}}
                        (this.priceType!== null && this.priceType!== "")
                        ){
                        console.log("Entro a setEnableSavePrices: IFFFF");
                        this.disableSavePrices = true;
                    } else {
                        this.disableSavePrices = false;
                    }

                }

}'

     @clear-markedcells.window="
        setPositionYMatrix();
        clearCells();
        setEnableSavePrices();
     "
>
<!-- Rectángulo de selección -->
<div
    x-ref="selectionBox"
    class="absolute border-2 border-blue-400 bg-blue-200/30 pointer-events-none z-50"
    :style="selectionBoxStyle()"
></div>

<form wire:submit="create">
    <div class="grid grid-cols-6 gap-4">
        <div class="col-span-5">
            {{ $this->form }}
        </div>
        <div class="col-span-1 flex flex-col justify-end">
            <button class="btn btn-primary btn-sm btn-block mb-1" type="submit">
                Cargar
            </button>
        </div>
    </div>
</form>
<hr class="my-2 border-t border-gray-300">

@if(count($matrix) > 0)
    <h4>
        <b>Codigo base:</b> {{ $baseCode }} 
        | @if($type) <span class="text-primary">POSITIVO</span> @else <span class="text-red-500">NEGATIVO</span> @endif
        | <button class="btn btn-xs btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-[1.2em]">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
        Historial    
        </button>
    </h4>

    @switch($action)
        @case('saldo')
        <p class="text-xl text-success text-center"><b>Saldos</b></p>
        @break
        @case('ingreso')
            <p class="text-xl text-success text-center"><b>Ingresos</b></p>
        <fieldset class="fieldset">
            <label class="label label-azteris">Cantidad</label>
            <div class="join">
                <input type="number" x-model="amount" min="1" class="input input-sm focus-within:outline-none" />
                <button class="btn btn-sm join-item" @click="changeSelected('{{ $action }}')">Cambiar</button>
                <!-- <button class="btn btn-sm join-item" @click="clearCells">Limpiar</button> -->
                <button class="btn btn-sm join-item" @click="modal_confirm_clear.showModal()">Limpiar</button>
                <button class="btn btn-primary btn-sm join-item"
                        @click="modal_confirm_income.showModal()"
{{--                        @click="$wire.call('saveIncome', markedAmountCells)"--}}
                        :disabled="markedAmountCells.length === 0"
                >
                    Registrar
                </button>
            </div>
        </fieldset>

        @break
        @case('entregas')
            <p class="text-xl text-success text-center"><b>Entregas</b></p>
            <fieldset class="fieldset">
                <label class="label label-azteris">Sucursal</label>
                <div class="join">
                    <select class="select select-sm join-item focus-within:outline-none" x-model="branchId" x-on:change="clearCells();">
                        <option value="" selected>Sucursal</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" x-model="amount" min="1" class="input input-sm focus-within:outline-none" placeholder="cantidad" />
                    <button class="btn btn-sm join-item" @click="changeSelected('{{ $action }}')">Cambiar</button>
                    <button class="btn btn-sm join-item" @click="modal_confirm_clear.showModal()">Limpiar</button>
                    <button class="btn btn-primary btn-sm join-item" @click="modal_confirm_delivery.showModal()"
                            :disabled="markedAmountCells.length === 0 || markedAmountCells.some(item => item.state === 'danger') || branchId=== null"
                    >
                        Registrar
                    </button>
                </div>
            </fieldset>
        @break
        @case('precios')
        <p class="text-xl text-success text-center"><b>Precios</b></p>
            <fieldset class="fieldset">
                <label class="label label-azteris">Precio</label>
                <div class="join">
                    <input type="number" x-model="amount" min="1" class="input input-sm focus-within:outline-none" placeholder="Ej: 0.00" />
                    <select class="select select-sm join-item focus-within:outline-none" x-model="branchId">
                        <option value="" selected>Almacen</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
{{--                    <select class="select select-sm join-item focus-within:outline-none" x-model="branchId" x-on:change="clearCells();">--}}
                    <select class="select select-sm join-item focus-within:outline-none" x-model="priceType" x-on:change="setEnableSavePrices()" >
                        <option value="" selected>Seleccione precio</option>
                        <option value="{{ \App\Models\Price::TYPE_NORMAL }}">{{ \App\Models\Price::TYPE_NORMAL }}</option>
                        <option value="{{ \App\Models\Price::TYPE_ESPECIAL }}">{{ \App\Models\Price::TYPE_ESPECIAL }}</option>
                        <option value="{{ \App\Models\Price::TYPE_MAYORISTA }}">{{ \App\Models\Price::TYPE_MAYORISTA }}</option>
                    </select>
                    <button class="btn btn-sm join-item" @click="changeSelected('{{ $action }}')">Cambiar</button>
                    <button class="btn btn-sm join-item" @click="modal_confirm_clear.showModal()">Limpiar</button>
                    <button class="btn btn-primary btn-sm join-item" @click="modal_confirm_change_prices.showModal()"
                            :disabled="!disableSavePrices"
                    >
                        Registrar
                    </button>
                </div>
            </fieldset>


        @break
    @endswitch

    <div x-text="messageError" class="text-error text-sm"></div>
    <hr class="my-2 border-t border-gray-300">

    <div class="table-container border border-gray-200 rounded-lg relative select-none overflow-x-auto"
         @mousedown.window="startSelection($event)"
         @mousemove.window="updateSelection($event)"
         @mouseup.window="endSelection"
    >
        <table id="t-matrix" x-ref="table" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky-header">
            <tr id="t-matrix">
                <th scope="col" class="">
                </th>
                @foreach ($uniqueCylinders as $i => $cylinder)
                    <th scope="col" class="px-1 py-1 text-center text-xs font-semibold text-white uppercase tracking-wider bg-success
                        @if($i == 8 || $i == 16) border-r-2 border-r-zinc-600 @endif
                    ">
                        {{ number_format($cylinder, 2) }}
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody
                class="bg-white divide-y divide-gray-200"
            >
            @foreach($matrix as $j => $row)
                <tr class="hover:bg-gray-50 @if($j == 4 || $j == 8 || $j == 16) border-b-2 border-b-zinc-600 @endif">
                    @foreach($row as $i => $opticalProperty)
                        @if($opticalProperty)
                            <td class="px-1 py-1 whitespace-nowrap text-xs text-center font-medium text-white
                                @if($type) bg-blue-500 @else bg-red-500 @endif
                                " >
                                {{ number_format($opticalProperty['sphere'], 2) }}
                            </td>
                            @break
                        @endif
                    @endforeach
                    @foreach($row as $i => $opticalProperty)
                        <td
                            title="{{ $opticalProperty['description'] }}"
                            data-cell-id="{{ $opticalProperty['id'] }}"
                            data-cell-amount="{{ $opticalProperty['amount'] }}"
                            @click="toggleCell({{ (int) $opticalProperty['id'] }})"
                            :class="markedAmountCells.some(item => item.id === {{ (int) $opticalProperty['id'] }})
                                ? (markedAmountCells.some(item => item.id === {{ (int) $opticalProperty['id'] }} && item.state=== 'success')
                                ? 'bg-green-400' : 'bg-red-400') : (markedCells.includes({{ (int) $opticalProperty['id'] }})
                                ? 'bg-green-200'
                                : 'bg-white')"
                            class="cursor-pointer px-1 py-1 whitespace-nowrap text-xs font-medium text-center border-t border-r
                            border-l
                            @if($j == 4 || $j == 8 || $j == 16) border-b-2 border-b-zinc-600 @else border-b @endif
                            @if($i == 8 || $i == 16) border-r-2 border-r-zinc-600 @endif

                            @if($j == 12 && $i == 4) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 13 && $i == 5) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 12 && $i == 12) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 13 && $i == 13) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 12 && $i == 20) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 13 && $i == 21) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 20 && $i == 4) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 21 && $i == 5) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 20 && $i == 12) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 21 && $i == 13) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 20 && $i == 20) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 21 && $i == 21) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif
                                "
                        >
                            <div class="tooltip" data-tip="{{ $opticalProperty['description'] }}">
                                <div class="badge badge-neutral">
                                    <i class="fa-solid fa-dollar-sign"></i>
                                </div>
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>

        <!-- Mostrar IDs seleccionados -->
        <dialog id="modal_confirm_income" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold">Registrar ingreso de productos</h3>
                <p class="py-4">Confirme que se va a registrar las cantidades de los productos</p>
                <label class="label">
                    <input x-model="sendBranch" type="checkbox" class="toggle toggle-accent" />
                    Enviar a Sucursal
                </label>
                <template x-if="sendBranch">
                    <fieldset class="fieldset">
                        <label class="label label-azteris">Sucursal</label>
                        <select class="select select-sm join-item focus-within:outline-none" x-model="branchId">
                            <option value="" selected>Sucursal ...</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </fieldset>
                </template>
                <div class="modal-action">
                    <form method="dialog">
                        <button 
                            class="btn btn-sm btn-secondary"
                            wire:loading.attr="disabled"
                            wire:target="save"
                        >
                            Cerrar
                        </button>
                    </form>
                    <button 
                        class="btn btn-sm btn-primary" 
                        @click="$wire.call('save', markedAmountCells, branchId); modal_confirm_income.close()"
                        wire:loading.attr="disabled"
                    >
                    Confirmar
                    </button>
                </div>
            </div>
        </dialog>

        <dialog id="modal_confirm_delivery" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold">Entrega de sucursal</h3>
                <p class="py-4">Confirme la entrega de productos a la sucursal seleccionada</p>
                <div class="modal-action">
                    <form method="dialog">
                        <!-- if there is a button in form, it will close the modal -->
                        <button class="btn btn-sm btn-secondary">Cerrar</button>
                        <button class="btn btn-sm btn-primary" @click="$wire.call('save', markedAmountCells, branchId)" >Confirmar</button>
                    </form>
                </div>
            </div>
        </dialog>

        <dialog id="modal_confirm_change_prices" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold">Cambiar precios de Almacenes</h3>
                <p class="py-4">Esta seguro de cambiar los precios seleccionados?</p>
                <div class="modal-action">
                    <form method="dialog">
                        <!-- if there is a button in form, it will close the modal -->
                        <button class="btn btn-sm btn-secondary">Cerrar</button>
{{--                        <button class="btn btn-sm btn-primary" @click="$wire.call('save', markedAmountCells, branchId)" >Confirmar</button>--}}
                        <button class="btn btn-sm btn-primary" @click="$wire.call('save', markedAmountCells, branchId, priceType)" >Confirmar</button>
                    </form>
                </div>
            </div>
        </dialog>

        <!-- Modal Confirmar Limpiar datos -->
        <dialog id="modal_confirm_clear" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold">Limpiar matriz</h3>
                <p class="py-4">¿Está seguro de que desea limpiar las celdas seleccionadas? Esta acción no se puede deshacer.</p>
                <div class="modal-action">
                    <form method="dialog">
                        <!-- if there is a button in form, it will close the modal -->
                        <button class="btn btn-sm btn-secondary">Cerrar</button>
                        <button class="btn btn-sm btn-primary" @click="clearCells" >Confirmar</button>
                    </form>
                </div>
            </div>
        </dialog>
    </div>
@endif

<x-filament-actions::modals />

    <div wire:loading wire:target="save, create" class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm transition-all">
        <div class="flex flex-col items-center gap-4 bg-base-100 p-8 rounded-2xl shadow-2xl">
            <span class="loading loading-spinner loading-lg text-primary"></span>
            <p class="text-lg font-bold text-base-content animate-pulse">Procesando...</p>
            <p class="text-xs text-base-content/60">Guardando cambios en el servidor</p>
        </div>
    </div>
</div>
