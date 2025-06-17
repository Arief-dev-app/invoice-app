<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GroupUser;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            GroupUser::factory()->create($group);
        }
    }
}
