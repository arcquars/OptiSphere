<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphedByMany;

class Category extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    use HasFactory;

    public function products(): MorphedByMan
    {
        return $this->morphedByMany(Product::class, 'categorizable');
    }

    public function services(): MorphedByMany
    {
        return $this->morphedByMany(Service::class, 'categorizable');
    }
}
