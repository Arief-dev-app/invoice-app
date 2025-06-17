<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Menu;
use App\Models\GroupUser;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Arief',
            'email' => 'ariieff.dev@gmail.com',
            'password' => Hash::make('gkQpA9qbRsaX!2N'), // Gantilah dengan password aman
        ]);

        $menus = [
            [
                'name' => 'Dashboard',
                'slug' => '/',
            ],
            [
                'name' => 'Company',
                'slug' => '/company',
            ],
        ];
    
        foreach ($menus as $menu) {
            Menu::create($menu);
        }

        $groups = [
            [
                'name' => 'Admin',
                'description' => 'Admin',
                'flag_active' => true,
            ],
            [
                'name' => 'IT',
                'description' => 'IT',
                'flag_active' => true,
            ],
        ];
    
        foreach ($groups as $group) {
            GroupUser::create($group);
        }
    }
}
