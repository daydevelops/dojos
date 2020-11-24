<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Dojo;
use Illuminate\Database\Seeder;

class DojoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Dojo::factory()->create(['user_id'=>User::first()]);
        Dojo::factory(3)->create(['user_id'=>User::all()[1]]);
        Dojo::factory(10)->create();
    }
}
