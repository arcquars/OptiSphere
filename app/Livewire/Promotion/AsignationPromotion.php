<?php

namespace App\Livewire\Promotion;

use App\Models\OpticalProperty;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\Service;
use Livewire\Component;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class AsignationPromotion extends Component
{
    public Promotion $promotion;

    // Estado del buscador
    public string $searchQuery = '';
    public string $searchType = 'product'; // 'product' o 'service'
    public Collection $searchResults;

    // Almacena los ítems seleccionados, usando una clave única: 'tipo_id'
    public array $attachedItems = []; // Ejemplo: ['product_1' => Model, 'service_5' => Model]

    protected $queryString = ['searchQuery'];

    protected array $rules = [
        'searchQuery' => 'nullable|string|max:50',
        'searchType' => 'required|in:product,service',
    ];

    /**
     * Se ejecuta al inicializar el componente.
     */
    public function mount(Promotion $promotion): void
    {
        $this->promotion = $promotion;
        $this->searchResults = Collection::make();

        // 1. Cargar ítems ya asociados y colocarlos en $attachedItems
        $items = $promotion->products->map(function ($item) {
            if($item->opticalProperties){
                return $this->formatAttachedItem($item, 'base_code');
            }
            return $this->formatAttachedItem($item, 'product');
        });

        $items = $items->merge($promotion->services->map(function ($item) {
            return $this->formatAttachedItem($item, 'service');
        }));

        // Convertir la colección a un array asociativo usando la clave única
        $this->attachedItems = $items->keyBy('key')->toArray();
    }

    /**
     * Listener que se dispara cuando $searchQuery cambia.
     * Ejecuta la lógica de búsqueda.
     */
    public function updatedSearchQuery(): void
    {
        $this->validateOnly('searchQuery');
        $this->performSearch();
    }

    /**
     * Ejecuta la búsqueda en el modelo seleccionado ($searchType).
     */
    public function performSearch(): void
    {
        if (empty($this->searchQuery) || strlen($this->searchQuery) < 3) {
            $this->searchResults = Collection::make();
            return;
        }

        $query = trim($this->searchQuery);
        if(strcmp($this->searchType, 'base_code') == 0){
            $this->searchResults = OpticalProperty::where('base_code', 'like', "%{$query}%")
                ->groupBy('base_code')
                ->limit(10)
                ->get(['base_code']);
        } else {
            $model = $this->searchType === 'product' ? Product::class : Service::class;
            

            $this->searchResults = $model::where('name', 'LIKE', "%{$query}%")
                ->orWhere('code', 'LIKE', "%{$query}%")
                ->limit(10) // Limitar resultados para mantener el rendimiento
                ->get(['id', 'name', 'code']);
        }
    }

    /**
     * Formatea un modelo para ser guardado en el array $attachedItems.
     */
    protected function formatAttachedItem(Model $item, string $type): array
    {
        return [
            'id' => $item->id,
            'type' => $type,
            'key' => "{$type}_{$item->id}",
            'name' => $item->name,
            'code' => $item->code,
        ];
    }

    /**
     * Agrega un ítem de los resultados a la tabla de ítems adjuntos.
     */
    public function addItem(string $id): void
    {
        if(strcmp($this->searchType, 'base_code') == 0){
            $products = Product::whereHas('opticalProperties', function($query) use ($id) {
                $query->where('base_code', 'like', $id);
            })->get(['id', 'name', 'code']);
            $type = "product";
            foreach($products as $pro){
                $key = "{$type}_{$pro->id}";

                if (array_key_exists($key, $this->attachedItems)) {
                    continue;
                }

                $this->attachedItems[$key] = $this->formatAttachedItem($pro, $type);
                // Opcional: limpiar la búsqueda después de añadir un elemento
                $this->searchQuery = '';
                $this->searchResults = Collection::make();
            }
        } else {
            $model = $this->searchType === 'product' ? Product::class : Service::class;
            $type = $this->searchType;
            $key = "{$type}_{$id}";

            if (array_key_exists($key, $this->attachedItems)) {
                session()->flash('warning', 'Este artículo ya está en la lista.');
                return;
            }

            $item = $model::find($id, ['id', 'name', 'code']);

            if ($item) {
                $this->attachedItems[$key] = $this->formatAttachedItem($item, $type);
                // Opcional: limpiar la búsqueda después de añadir un elemento
                $this->searchQuery = '';
                $this->searchResults = Collection::make();
            }
        }
        
    }

    /**
     * Remueve un ítem de la tabla de ítems adjuntos.
     */
    public function removeItem(string $key): void
    {
        if (array_key_exists($key, $this->attachedItems)) {
            unset($this->attachedItems[$key]);
            // Reindexar el array para evitar problemas de Livewire
            $this->attachedItems = array_values($this->attachedItems);
            $this->attachedItems = collect($this->attachedItems)->keyBy('key')->toArray();
        }
    }

    /**
     * Sincroniza las relaciones polimórficas de la promoción.
     */
    public function save(): void
    {
        // 1. Separar IDs por tipo
        $productIds = collect($this->attachedItems)
            ->where('type', 'product')
            ->pluck('id')
            ->toArray();

        $serviceIds = collect($this->attachedItems)
            ->where('type', 'service')
            ->pluck('id')
            ->toArray();

        try {
            // 2. Sincronizar
            $this->promotion->products()->sync($productIds);
            $this->promotion->services()->sync($serviceIds);

            session()->flash('success', '¡La asignación de productos/servicios ha sido actualizada correctamente!');

        } catch (\Exception $e) {
            Log::error("Error al guardar la asignación de promoción: " . $e->getMessage());
            session()->flash('error', 'Error al guardar la asignación. Consulta los logs para más detalles.');
        }
    }


    public function render()
    {
        return view('livewire.promotion.asignation-promotion');
    }
}
