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
                    init() {
                        console.log(`ccc2`, this.cells);
                    },
                toggleCell(id) {
                    if (this.markedCells.includes(id)) {
                        this.markedCells = this.markedCells.filter(item => item !== id)
                        this.markedAmountCells = this.markedAmountCells.filter(item => item.id !== id)

                        if(this.cells[id]){
                            this.cells[id] = "-"
                        }
                    } else {
                        this.markedCells.push(id)
                    }
                },
                changeSelected() {
                    if (this.amount === "") return  // no hacer nada si está vacío
                    this.markedCells.forEach(id => {
                        this.cells[id] = this.amount
                        if (this.markedAmountCells.some(item => item.id === id)) {
                            this.markedAmountCells = this.markedAmountCells.filter(item => item.id !== id)
                        }
                        this.markedAmountCells.push({id, amount: this.amount});
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
                }
}'

     @clear-markedcells.window="
        // Actualiza la variable 'stock' con el dato recibido del evento
        clearCells();
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
    <h4><b>Codigo base:</b> {{ $baseCode }} | @if($type) <span class="text-primary">POSITIVO</span> @else <span class="text-red-500">NEGATIVO</span> @endif</h4>

    @switch($action)
        @case('saldo')
        <p class="text-xl text-success text-center"><b>Saldos</b></p>
        @break
        @case('ingreso')
            <p class="text-xl text-success text-center"><b>Ingresos</b></p>
        <fieldset class="fieldset">
{{--            <legend class="fieldset-legend">Ingreso</legend>--}}
            <label class="label label-azteris">Cantidad</label>
            <div class="join">
                <input type="number" x-model="amount" min="1" class="input input-sm" />
                <button class="btn btn-sm join-item" @click="changeSelected">Cambiar</button>
                <button class="btn btn-sm join-item" @click="clearCells">Limpiar</button>
                <button class="btn btn-primary btn-sm join-item" @click="$wire.call('saveIncome', markedAmountCells)"
                        :disabled="markedAmountCells.length === 0"
                >
                    Registrar
                </button>
            </div>
        </fieldset>

        @break
        @case('precios')
        Precios::
        @break
    @endswitch

    <hr class="my-2 border-t border-gray-300">

    <div class="table-container border border-gray-200 rounded-lg relative select-none"
         @mousedown.window="startSelection($event)"
         @mousemove.window="updateSelection($event)"
         @mouseup.window="endSelection"
    >
        <table x-ref="table" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky-header">
            <tr>
                <th scope="col" class="">
                </th>
                @foreach ($uniqueCylinders as $cylinder)
                    <th scope="col" class="px-2 py-2 text-center text-xs font-semibold text-white uppercase tracking-wider bg-success">
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
                            <td class="px-2 py-2 whitespace-nowrap text-xs font-medium text-white @if($type) bg-blue-500 @else bg-red-500 @endif" >
                                {{ number_format($opticalProperty['sphere'], 2) }}
                            </td>
                            @break
                        @endif
                    @endforeach
                    @foreach($row as $i => $opticalProperty)
                        <td
                            data-cell-id="{{ $opticalProperty['id'] }}"
                            @click="toggleCell({{ (int) $opticalProperty['id'] }})"
                            :class="markedAmountCells.some(item => item.id === {{ (int) $opticalProperty['id'] }})
                                ? 'bg-green-400' : markedCells.includes({{ (int) $opticalProperty['id'] }})
                                ? 'bg-green-100'
                                : 'bg-white'"
                            class="cursor-pointer px-1 py-1 whitespace-nowrap text-xs font-medium text-center border"
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
{{--        <div class="mt-3">--}}
{{--            <strong>Celdas marcadas:</strong>--}}
{{--            <span x-text="markedCells.join(', ')"></span>--}}
{{--        </div>--}}
{{--        <div class="mt-3">--}}
{{--            <strong>Celdas con cantidad:</strong>--}}
{{--            <span x-text="markedAmountCells.join(', ')"></span>--}}
{{--        </div>--}}
{{--        <div class="mt-3">--}}
{{--            <strong>Cells:</strong>--}}
{{--            <span x-text="cells"></span>--}}
{{--        </div>--}}

    </div>
@endif

<x-filament-actions::modals />
</div>
