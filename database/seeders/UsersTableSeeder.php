<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
        ]);

        \App\Models\User::create([
            'name' => 'Agent One',
            'email' => 'agent1@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'agent',
        ]);

        \App\Models\User::create([
            'name' => 'Agent Two',
            'email' => 'agent2@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'agent',
        ]);
    }
}
