<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un usuario con rol de administrador
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@cerisier.net',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        // Crear un usuario con rol de responsable de sucursal
        $branchManager = User::create([
            'name' => 'Responsable Sucursal',
            'email' => 'branch.manager@cerisier.net',
            'password' => bcrypt('password'),
        ]);
        $branchManager->assignRole('branch_manager');

        // Crear un usuario con rol de Contador
        $wholesaler = User::create([
            'name' => 'Contador',
            'email' => 'accountant@cerisier.net',
            'password' => bcrypt('password'),
        ]);
        $wholesaler->assignRole('accountant');

        // Crear un usuario con rol de Coordinador de sucursales
        $branchCoordinator = User::create([
            'name' => 'Coordinador',
            'email' => 'coordinator@cerisier.net',
            'password' => bcrypt('password'),
        ]);
        $branchCoordinator->assignRole('branch_coordinator');
    }
}
