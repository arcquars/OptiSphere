<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Branch extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'address',
        'is_active'
    ];

    use HasFactory;

    /**
     * The users that belong to the branch.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function prices()
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function siatProperty(): HasOne
    {
        return $this->hasOne(SiatProperty::class);
    }

    public function getIsFacturableAttribute(): bool {
        if($this->siatProperty() == null) {
            return false;
        }
        
        return $this->siatProperty->is_actived && $this->siatProperty->is_validated;
    }
    
    public function isOpenCashBoxClosingByUser($userId){
        return CashBoxClosing::where('branch_id', $this->id)->where('user_id', $userId)->where('status', '=', CashBoxClosing::STATUS_OPEN)->exists();
    }

    public function getCashBoxClosingByUser($userId){
        return CashBoxClosing::where('branch_id', $this->id)->where('user_id', $userId)->where('status', '=', CashBoxClosing::STATUS_OPEN)->first();
    }
}
