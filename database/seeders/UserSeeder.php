<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Arief',
            'email' => 'ariieff.dev@gmail.com',
            'password' => Hash::make('gkQpA9qbRsaX!2N'), // Gantilah dengan password aman
        ]);

        // User::factory()->count(10)->create(); // Jika pakai factory untuk dummy
    }
}
