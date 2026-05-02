<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Type;
use App\Models\Unit;
use App\Models\User;
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
        // User::factory(10)->create();

        $user1 = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'admin', 'password' => Hash::make('admin')]
        );
        $user1->assignRole('super_admin');

        $user2 = User::firstOrCreate(
            ['email' => 'test@gmail.com'],
            ['name' => 'test', 'password' => Hash::make('test')]
        );
        $user2->assignRole('panel_user');

        // Customer::factory()->count(0)->create();
        // Type::factory()->count(0)->create();
        // Category::factory()->count(0)->create();
        // Unit::factory()->count(1)->create();
    }
}
