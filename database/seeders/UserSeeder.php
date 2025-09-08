<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'username' => 'admin',
                'password' => 'password',
            ]
        );
        $admin->assignRole('adm');

        $medico = User::firstOrCreate(
            ['email' => 'medico@example.com'],
            [
                'name' => 'Medico',
                'username' => 'medico',
                'password' => 'password',
            ]
        );
        $medico->assignRole('medico');

        $enfermeiro = User::firstOrCreate(
            ['email' => 'enfermeiro@example.com'],
            [
                'name' => 'Enfermeiro',
                'username' => 'enfermeiro',
                'password' => 'password',
            ]
        );
        $enfermeiro->assignRole('enfermeiro');
    }
}
