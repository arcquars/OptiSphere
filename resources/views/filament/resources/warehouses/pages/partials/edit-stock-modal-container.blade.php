{{-- resources/views/filament/resources/warehouses/pages/partials/edit-stock-modal-container.blade.php --}}
<div>
    @if($record)
        @livewire('warehouse.edit-warehouse-stock-item-modal', [
            // 'recordId' => $record->id,
            // 'historyId' => $record->id,

            'historyId' => $record->warehouse_m->id, 
            'action' => $record->movement_type, 
            'productId' => $record->warehouseStock->product_id, 
            'warehouseId' => $record->warehouseStock->warehouse_id
        ], key($record->id))
    @endif
</div>