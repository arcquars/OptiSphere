// Definimos el componente Alpine.js
export default (data) => ({
    markedCells: [],
    markedAmountCells: [],
    amount: "",
    selecting: false,
    startX: 0,
    startY: 0,
    currentX: 0,
    currentY: 0,
    cells: {},
    initialStocks: data.initialStocks?? {},
    test: data.test + "2",
    init() {
        console.log('cccc 0')
        // Al inicializar el componente, poblamos el objeto "cells" con los datos iniciales
        // Esto permite que el stock sea reactivo y editable en el frontend
        for (let i = 0; i < this.initialStocks.length; i++) {
            for (let op in this.initialStocks[i]) {
                this.cells[this.initialStocks[i][op].id] = "-";
            }
        }
        console.log(`ccc2`, this.cells);
    },
    toggleCell(id) {
        console.log("x00", this.markedCells);
        if (this.markedCells.includes(id)) {
            this.markedCells = this.markedCells.filter(item => item !== id)
            this.markedAmountCells = this.markedAmountCells.filter(item => item.id !== id)

            if (this.cells[id]) {
                this.cells[id] = "-"
            }
        } else {
            this.markedCells.push(id)
        }
        console.log("x0", this.markedCells);
    },
    changeSelected() {
        console.log("x1", this.markedCells);
        if (this.amount === "") return  // no hacer nada si está vacío
        this.markedCells.forEach(id => {
            this.cells[id] = this.amount
            console.log("x2::", this.amount);
            if (this.markedAmountCells.some(item => item.id === id)) {
                console.log("x3");
                this.markedAmountCells = this.markedAmountCells.filter(item => item.id !== id)
            }
            this.markedAmountCells.push({id, amount: this.amount});
        })
        console.log("x4");
        this.markedCells = [];
    },
    clearSelected() {
        console.log("x44", this.markedCells);
        this.markedAmountCells = [];
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
    uploadText(id, accion) {
        if (accion === "saldo")
            return this.cells[id] ? this.cells[id] : "4";
        return this.cells[id] ? this.cells[id] : "-";

    }
});
