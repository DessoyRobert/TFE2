<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // User standard
        User::create([
            'name'     => 'Utilisateur Test',
            'email'    => 'user@example.com',
            'password' => Hash::make('password'), // mot de passe simple à changer en prod
            'is_admin' => false,
        ]);

        // Admin
        User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'), // mot de passe simple à changer en prod
            'is_admin' => true,
        ]);
    }
}
