<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'accountant', 'guard_name' => 'web']);
        Role::create(['name' => 'branch-manager', 'guard_name' => 'web']);
        Role::create(['name' => 'branch-coordinator', 'guard_name' => 'web']);
    }
}
