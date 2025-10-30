<?php

namespace App\Models;

use App\Contracts\SalableInterface;
use App\Traits\HasPricesAndPromotions;
use App\Traits\HasPricesByBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model implements SalableInterface
{
    const QUANTITY_DEFAULT = 50;

    use HasPricesAndPromotions;
    use HasPricesByBranch;
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'description', 'path_image','is_active'];

    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

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

    public function getPromotionById(int $promotionId): ?Promotion
    {
        return $this->promotions()
            ->where('promotions.id', $promotionId) // Filtrar por el ID de la tabla 'promotions'
            ->first();
    }
}
