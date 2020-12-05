<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_ENV') == 'local') {
            $this->call([
                CategorySeeder::class,
                UserSeeder::class,
                DojoSeeder::class,
                StripeProductSeeder::class
            ]);
        } else {
            $this->call([ProductionSeeder::class]);
        }
    }
}
