<?php

namespace App\Models;

use App\Traits\HasPricesByBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Service extends Model
{
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
}
