<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@biracarvalho.com.br'],
            [
                'name' => 'Administrador',
                'email' => 'admin@biracarvalho.com.br',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}

