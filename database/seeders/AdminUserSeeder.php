<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@chatboalar.local');
        $password = env('ADMIN_PASSWORD');

        if (blank($password)) {
            throw new RuntimeException('Define ADMIN_PASSWORD en el entorno antes de ejecutar los seeders.');
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Administrador'),
                'role' => 'administrador',
                'active' => true,
                'password' => $password,
            ]
        );
    }
}
