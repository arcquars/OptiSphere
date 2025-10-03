<?php

namespace App\Models;

use App\Traits\HasPricesByBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Service extends Model
{
    const QUANTITY_DEFAULT = 50;
    use HasPricesByBranch;

    protected $fillable = ['name', 'code', 'description', 'path_image','is_active'];

    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable');
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
