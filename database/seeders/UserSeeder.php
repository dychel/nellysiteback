<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Utilisateur Administrateur
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'Nelly',
            'email' => 'admin@nelly.com',
            'phone' => '+237612345678',
            'password' => Hash::make('password123'),
            'is_admin' => true,
            'remarks' => 'Administrateur principal du site',
        ]);

        // Utilisateurs normaux
        User::create([
            'first_name' => 'Alice',
            'last_name' => 'Dupont',
            'email' => 'alice@example.com',
            'phone' => '+237612345679',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'remarks' => 'Client fidèle',
        ]);

        User::create([
            'first_name' => 'Bob',
            'last_name' => 'Martin',
            'email' => 'bob@example.com',
            'phone' => '+237612345680',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'remarks' => 'Nouveau client',
        ]);

        User::create([
            'first_name' => 'Clara',
            'last_name' => 'Johnson',
            'email' => 'clara@example.com',
            'phone' => '+237612345681',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'remarks' => 'Client professionnel',
        ]);

        // Utilisateurs supplémentaires
        User::create([
            'first_name' => 'David',
            'last_name' => 'Wilson',
            'email' => 'david@example.com',
            'phone' => '+237612345682',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        User::create([
            'first_name' => 'Emma',
            'last_name' => 'Brown',
            'email' => 'emma@example.com',
            'phone' => '+237612345683',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        User::create([
            'first_name' => 'Frank',
            'last_name' => 'Davis',
            'email' => 'frank@example.com',
            'phone' => '+237612345684',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        User::create([
            'first_name' => 'Grace',
            'last_name' => 'Miller',
            'email' => 'grace@example.com',
            'phone' => '+237612345685',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        User::create([
            'first_name' => 'Henry',
            'last_name' => 'Garcia',
            'email' => 'henry@example.com',
            'phone' => '+237612345686',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        User::create([
            'first_name' => 'Ivy',
            'last_name' => 'Rodriguez',
            'email' => 'ivy@example.com',
            'phone' => '+237612345687',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        $this->command->info('10 utilisateurs créés avec succès !');
        $this->command->info('Admin: admin@nelly.com / password123');
        $this->command->info('Utilisateur: alice@example.com / password123');
    }
}