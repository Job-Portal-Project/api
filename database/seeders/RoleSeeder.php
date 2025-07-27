<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach(['Admin', 'Moderator', 'Company', 'Candidate'] as $role) {
            Role::query()->firstOrCreate(['name' => $role], ['name' => $role]);
        }
    }
}
