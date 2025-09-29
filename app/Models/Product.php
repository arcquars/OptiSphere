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
}
