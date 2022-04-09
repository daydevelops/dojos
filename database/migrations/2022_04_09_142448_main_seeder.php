<?php

use Database\Seeders\CategorySeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DojoSeeder;
use Database\Seeders\ProductionSeeder;
use Database\Seeders\StripeProductSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Migrations\Migration;

class MainSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (config('app.env') == 'production' || config('app.env') == 'staging') {
            (new DatabaseSeeder())->call(ProductionSeeder::class);
        } else if (config('app.env') == 'local') {
            (new DatabaseSeeder())->call(UserSeeder::class);
            (new DatabaseSeeder())->call(CategorySeeder::class);
            (new DatabaseSeeder())->call(DojoSeeder::class);
            (new DatabaseSeeder())->call(StripeProductSeeder::class);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
