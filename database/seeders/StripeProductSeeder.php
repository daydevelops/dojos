<?php

namespace Database\Seeders;

use App\Models\StripeProduct;
use Illuminate\Database\Seeder;

class StripeProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StripeProduct::insert([
            'product_id' => "free_plan",
            'description' => "No Plan"
        ]);
        StripeProduct::insert([
            'product_id' => env("STANDARD_MONTHLY_PLAN_ID"),
            'description' => "5 CAD/month"
        ]);
        StripeProduct::insert([
            'product_id' => env("STANDARD_YEARLY_PLAN_ID"),
            'description' => "50 CAD/year"
        ]);
        StripeProduct::insert([
            'product_id' => env("PREMIUM_MONTHLY_PLAN_ID"),
            'description' => "10 CAD/month"
        ]);
        StripeProduct::insert([
            'product_id' => env("PREMIUM_YEARLY_PLAN_ID"),
            'description' => "100 CAD/year"
        ]);
    }
}
