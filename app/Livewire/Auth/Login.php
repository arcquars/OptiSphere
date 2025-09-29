<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

//        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        Log::info("Pdm 33:: " . Auth::user()->hasRole('admin'));
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->hasRole('admin')) {
                $this->redirectRoute('filament.admin.pages.dashboard');
            } elseif ($user->hasRole('accountant')) {
                $this->redirectIntended(
                    default: route('filament.accountant.pages.dashboard', absolute: false),
                    navigate: true
                );
            } elseif ($user->hasRole('branch_manager')) {
                $this->redirectIntended(
                    default: route('filament.branch-manager.pages.dashboard', absolute: false),
                    navigate: true
                );
            } elseif ($user->hasRole('branch_coordinator')) {
                $this->redirectIntended(
                    default: route('filament.branch-coordinator.pages.dashboard', absolute: false),
                    navigate: true
                );
            }
        }
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
