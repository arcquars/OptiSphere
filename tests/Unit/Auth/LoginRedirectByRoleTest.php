<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    foreach (['admin', 'branch-manager', 'branch-coordinator', 'frequent-customer'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

/** Crea un usuario activo con el rol dado. */
function userWithRole(string $role): User
{
    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);
    $user->assignRole($role);

    return $user;
}

it('redirige al panel de cliente frecuente tras el login', function (): void {
    $user = userWithRole('frequent-customer');

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('filament.frequent-customer.pages.dashboard'));

    expect(auth()->check())->toBeTrue();
});

it('sigue redirigiendo a los paneles de los demás roles', function (string $role, string $routeName): void {
    $user = userWithRole($role);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route($routeName));
})->with([
    ['admin', 'filament.admin.pages.dashboard'],
    ['branch-manager', 'filament.branch-manager.pages.dashboard'],
    ['branch-coordinator', 'filament.branch-coordinator.pages.dashboard'],
]);
