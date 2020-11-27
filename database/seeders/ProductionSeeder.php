<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name'=>env('ADMIN_NAME'),
            'email'=> env('ADMIN_EMAIL'),
            'password' => Hash::make(env('ADMIN_PASSWORD')),
            'is_admin' => true
        ]);
        Category::factory()->create(['name'=>'All']);
        Category::factory()->create(['name'=>'None']);
    }
}
