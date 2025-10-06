<?php

namespace App\Models;

use App\Contracts\SalableInterface;
use App\Traits\HasPricesAndPromotions;
use App\Traits\HasPricesByBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Log;

class Product  extends Model implements SalableInterface
{
    use HasPricesAndPromotions;
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

    /**
     * Obtiene las promociones aplicables a este servicio (Relación Polimórfica de Muchos a Muchos).
     */
    public function promotions(): MorphToMany
    {
        return $this->morphToMany(Promotion::class, 'promotionable')
            ->where('is_active', true) // Solo promociones activas
            ->where('start_date', '<=', now()) // Que ya hayan iniciado
            ->where('end_date', '>=', now()); // Que aún no hayan terminado
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

    public function stockByBranch($branchId){
        $productStock = ProductStock::where('product_id', $this->id)
            ->where('branch_id', $branchId)->first();
        if($productStock)
            return $productStock->quantity;
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

    public function getPriceByType($branchId = null, $priceType = "normal"): float
    {
        $price = $this->prices()->where('branch_id', $branchId)->where('type', '=', $priceType)->first();
        if($price)
            return $price->price;
        return 0;
    }

    public function getUrlImage(){
        if($this->image_path){
            return asset('/storage/' . $this->image_path);
        }

        return asset('/img/cerisier-no-image.png');
    }
}
