<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * The branches that belong to the user.
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class);
    }

//    public function canAccessPanel(Panel $panel): bool
//    {
//        if ($panel->getId() === 'admin') {
//            return str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail();
//        }
//
//        return true;
//    }
    public function canAccessPanel(Panel $panel): bool
    {
        Log::info("1 Eeee: " . $panel->getId() . " || rol:: " . $this->hasRole('admin'));

        if($this->hasRole('admin') && $this->is_active){
            return $this->hasVerifiedEmail();
        } else if($this->hasRole('accountant') && $panel->getId() === 'accountant' && $this->is_active) {
            return $this->hasVerifiedEmail();
        } else if($this->hasRole('branch_manager') && $panel->getId() === 'branch-manager' && $this->is_active) {
            return $this->hasVerifiedEmail();
        } else if($this->hasRole('branch_coordinator') && $panel->getId() === 'branch-coordinator' && $this->is_active) {
            return $this->hasVerifiedEmail();
        }

//        if ($panel->getId() === 'admin') {
//            Log::info("2 Eeee: " . $panel->getId());
//            return $this->hasVerifiedEmail();
//        } else if($panel->getId() === 'branch-manager'){
//            Log::info("3 Eeee: " . $panel->getId());
//            return $this->hasVerifiedEmail();
//        }

        return false;
    }
}
