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
            'name'      => config('app.admin.name'),
            'email'     => config('app.admin.email'),
            'password'  => Hash::make(config('app.admin.password'),),
            'is_admin'  => true
        ]);
        Category::factory()->create(['name'=>'All']);
        Category::factory()->create(['name'=>'None']);

        $this->call([
            StripeProductSeeder::class
        ]);
    }
}
