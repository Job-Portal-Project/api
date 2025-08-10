<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (RoleEnum::values() as $role) {
            Role::query()->firstOrCreate(['name' => $role], ['name' => $role, 'guard_name' => 'api']);
        }
    }
}
