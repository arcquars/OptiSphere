<?php

namespace App\Models;

use App\Traits\HasPricesByBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    use HasPricesByBranch;
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'image_path',
        'description',
        'is_active',
        'supplier_id'
    ];

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /** Esto devuelve los precios de todas las Sucursales y nulos que son Almacenes
     * @return MorphMany
     */
    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function opticalProperties(): HasOne
    {
        return $this->hasOne(OpticalProperty::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function stockByStockWarehouse($warehouseId){
        return WarehouseStock::where('product_id', $this->id)->where('warehouse_id', $warehouseId)->first();
    }

    public function stockByStockBranch($branchId){
        return ProductStock::where('product_id', $this->id)->where('branch_id', $branchId)->first();
    }

    public function stockByWarehouse($warehouseId){
        $warehouseStock = WarehouseStock::where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)->first();
        $quantity = 0;
        if($warehouseStock)
            $quantity = $warehouseStock->quantity;
        return $quantity;
    }

    public function stockByBranch($brachId){
        $warehouseStock = ProductStock::where('product_id', $this->id)
            ->where('branch_id', $brachId)->first();
        if($warehouseStock)
            return $warehouseStock->quantity;
        return 0;
    }

    public function textPrices($branchId = null){
        $prices = $this->prices()->where('branch_id', $branchId)->get();
        $text = [];
        foreach ($prices as $price){
            $text[]= strtoupper($price->type) . " " . $price->price;
        }
//        return implode(",", $text);
        return $text;
    }
}
