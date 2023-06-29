<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'user_id' => '00000',
            'role' => 'admin',
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'admin@admin.com',
            'phone' => '08050000000',
            'gender' => 'Male',
            'dob' => null,
            'password' => Hash::make('123456')
        ]);

    }
}
