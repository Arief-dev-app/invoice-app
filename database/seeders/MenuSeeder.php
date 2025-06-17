<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'name' => 'Admin',
                'slug' => 'Admin',
            ],
            [
                'name' => 'IT',
                'slug' => 'IT',
            ],
        ];
    
        foreach ($menus as $menu) {
            Menu::factory()->create($menu);
        }
    }
}
