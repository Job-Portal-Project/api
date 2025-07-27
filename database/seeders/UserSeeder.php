<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = collect([
            'shahmal1yevv@proton.me' => ['role' => Role::ADMIN->value, 'name' => 'Eldar Shahmaliyev'],
            'qasimzadeali4@gmail.com' => ['role' => Role::ADMIN->value, 'name' => 'Ali Qasimzade'],
            'idrismikayil@gmail.com' => ['role' => Role::ADMIN->value, 'name' => 'Idris Mikayilov'],
        ]);

        $data->map(function ($user, $email): User {
            $user = User::query()->firstOrCreate(['email' => $email], [
                'email' => $email,
                'name' => $user['name'],
                'password' => Hash::make(Str::random()),
            ]);

            $user->assignRole($user['role']);

            return $user;
        });
    }
}
