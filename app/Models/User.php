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
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

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
        return $this->belongsToMany(Branch::class)
            ->where('is_active', true);
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
        // 1. Si el usuario no está activo, no entra a ningún lado
        if (!$this->is_active) {
            return false; 
        }

        $panelId = $panel->getId();

        // 2. Administrador: Acceso total
        if ($this->hasRole('admin')) {
            return true;
        }

        // 3. Lógica por Panel
        if ($panelId === 'accountant' && $this->hasRole('accountant')) {
            return true;
        }

        if ($panelId === 'branch-manager' && $this->hasRole('branch-manager')) {
            return true;
        }

        if ($panelId === 'branch-coordinator' && $this->hasRole('branch-coordinator')) {
            return true;
        }

        // 4. Si llegó aquí, no tiene acceso al panel actual.
        // Importante: No cerramos sesión aquí directamente porque Filament 
        // chequea esto en cada carga. Si devolvemos false, Filament lanzará un 403.
        return false;
    }
}
