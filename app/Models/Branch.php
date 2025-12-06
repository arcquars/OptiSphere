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

    public function amyrConnectionBranch(): HasOne
    {
        return $this->hasOne(AmyrConnectionBranch::class);
    }

    public function getIsFacturableAttribute(): bool {
        $amyrConnectionBranch = $this->amyrConnectionBranch;
        if($amyrConnectionBranch == null) {
            return false;
        }
        if(!$amyrConnectionBranch->is_actived) {
            return false;
        }
        
        if($amyrConnectionBranch->is_actived && $amyrConnectionBranch->token == null) {
            return false;
        }   
        return true;
    }
    
    public function isOpenCashBoxClosingByUser($userId){
        return CashBoxClosing::where('branch_id', $this->id)->where('user_id', $userId)->where('status', '=', CashBoxClosing::STATUS_OPEN)->exists();
    }

    public function getCashBoxClosingByUser($userId){
        return CashBoxClosing::where('branch_id', $this->id)->where('user_id', $userId)->where('status', '=', CashBoxClosing::STATUS_OPEN)->first();
    }

}
