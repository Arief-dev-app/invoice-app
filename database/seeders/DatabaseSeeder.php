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
                'name' => 'Master',
                'seq' => '1',
                'code' => 'MENU01',
                'slug' => '',
                'parent_id' => null,
            ],
            [
                'name' => 'Produk',
                'seq' => '11',
                'code' => 'MENU01-1',
                'slug' => '/produk',
                'parent_id' => 1,
            ],
            [
                'name' => 'Customer',
                'seq' => '12',
                'code' => 'MENU01-2',
                'slug' => '/customer',
                'parent_id' => 1,
            ],
            [
                'name' => 'Supplier',
                'seq' => '13',
                'code' => 'MENU01-3',
                'slug' => '/supplier',
                'parent_id' => 1,
            ],
            [
                'name' => 'Jasa',
                'seq' => '14',
                'code' => 'MENU01-4',
                'slug' => '/jasa',
                'parent_id' => 1,
            ],
            [
                'name' => 'Kategori',
                'seq' => '15',
                'code' => 'MENU01-5',
                'slug' => '/kategori',
                'parent_id' => 1,
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
                'name' => 'Kasir',
                'description' => 'Kasir',
                'flag_active' => true,
            ],
        ];
    
        foreach ($groups as $group) {
            GroupUser::create($group);
        }
    }
}
