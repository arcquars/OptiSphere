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
                    warehouseId: null,
                    messageError: "",
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
                },
                changeSelected(action) {
                    this.messageError = "";
                    if (this.amount === ""){
                        this.messageError = "Cantidad es requerido";
                        return  // no hacer nada si está vacío
                    }
                    this.markedCells.forEach(id => {
                        if(action === "devolucion"){
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
{{--                            this.cells[id] = this.amount--}}
{{--                            if (this.markedAmountCells.some(item => item.id === id)) {--}}
{{--                                this.markedAmountCells = this.markedAmountCells.filter(item => item.id !== id)--}}
{{--                            }--}}
{{--                            this.markedAmountCells.push({id, amount: this.amount, state: "success"});--}}
                        }
                    })
                    this.markedCells = [];
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
                }
}'

     @clear-markedcells.window="
        setPositionYMatrix();
        clearCells();
     "
>
    <!-- Rectángulo de selección -->
    <div
        x-ref="selectionBox"
        class="absolute border-2 border-blue-400 bg-blue-200/30 pointer-events-none z-50"
        :style="selectionBoxStyle()"
    ></div>

    <form wire:submit="loadMatrixProduct">
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
        <h4><b>Codigo base:</b> {{ $baseCode }} | @if($type) <span class="text-primary">POSITIVO</span> @else <span class="text-red-500">NEGATIVO</span> @endif</h4>

        @switch($action)
            @case('saldo')
            <p class="text-xl text-success text-center"><b>Saldos</b></p>
            @break
            @case('devolucion')
            <p class="text-xl text-success text-center"><b>Devoluciones</b></p>
            <fieldset class="fieldset">
                <label class="label label-azteris">Almacen</label>
                <div class="join">
                    <select class="select select-sm" x-model="warehouseId">
                        <option value="" selected>Almacen...</option>
                        @foreach($warehouses as $warehouse)
                        <option value="warehouse-{{ $warehouse->id }}">Almacen - {{ $warehouse->name }}</option>
                        @endforeach
                        @foreach($branches as $branch)
                            <option value="branch-{{ $branch->id }}">Sucursal - {{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" x-model="amount" min="1" class="input input-sm" />
                    <button class="btn btn-sm join-item" @click="changeSelected('{{ $action }}')">Cambiar</button>
                    <button class="btn btn-sm join-item" @click="clearCells">Limpiar</button>
                    <button class="btn btn-primary btn-sm join-item"
                            @click="modal_confirm_refund.showModal()"
                            {{--                        @click="$wire.call('saveIncome', markedAmountCells)"--}}
                            :disabled="markedAmountCells.length === 0 || markedAmountCells.some(item => item.state === 'danger') || warehouseId === null"
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
            <table x-ref="table" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky-header">
                <tr id="t-matrix">
                    <th scope="col" class="">
                    </th>
                    @foreach ($uniqueCylinders as $cylinder)
                        <th scope="col" class="px-1 py-1 text-center text-xs font-semibold text-white uppercase tracking-wider bg-success">
                            {{ number_format($cylinder, 2) }}
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody
                    class="bg-white divide-y divide-gray-200"
                >
                @foreach($matrix as $row)
                    <tr class="hover:bg-gray-50">
                        @foreach($row as $i => $opticalProperty)
                            @if($opticalProperty)
                                <td class="px-1 py-1 whitespace-nowrap text-xs text-center font-medium text-white @if($type) bg-blue-500 @else bg-red-500 @endif" >
                                    {{ number_format($opticalProperty['sphere'], 2) }}
                                </td>
                                @break
                            @endif
                        @endforeach
                        @foreach($row as $i => $opticalProperty)
                            <td
                                data-cell-id="{{ $opticalProperty['id'] }}"
                                data-cell-amount="{{ $opticalProperty['amount'] }}"
                                @click="toggleCell({{ (int) $opticalProperty['id'] }})"
                                    :class="markedAmountCells.some(item => item.id === {{ (int) $opticalProperty['id'] }})
                                ? (markedAmountCells.some(item => item.id === {{ (int) $opticalProperty['id'] }} && item.state=== 'success')
                                ? 'bg-green-400' : 'bg-red-400') : (markedCells.includes({{ (int) $opticalProperty['id'] }})
                                ? 'bg-green-200'
                                : 'bg-white')"
                                class="cursor-pointer px-1 py-1 whitespace-nowrap text-xs font-medium text-center border border"
                            >
                                <div
                                    x-text="uploadText({{ $opticalProperty['id'] }}, {{ $opticalProperty['amount'] }})"
                                >
                                </div>

                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>

            <!-- Mostrar IDs seleccionados -->
            <dialog id="modal_confirm_refund" class="modal">
                <div class="modal-box">
                    <h3 class="text-lg font-bold">Registrar devoluciones de productos</h3>
                    <p class="py-4">Confirme que se va a registrar las cantidades de los productos</p>
                    <div class="modal-action">
                        <form method="dialog">
                            <!-- if there is a button in form, it will close the modal -->
                            <button class="btn btn-sm btn-secondary">Cerrar</button>
                            <button class="btn btn-sm btn-primary" @click="$wire.call('save', markedAmountCells, warehouseId)" >Confirmar</button>
                        </form>
                    </div>
                </div>
            </dialog>


        </div>
    @endif

    <x-filament-actions::modals />
</div>
