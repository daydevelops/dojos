<?php

namespace Database\Seeders;

use App\Models\StripeProducts;
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
        StripeProducts::insert([
            'stripe_id'=>null,
            'description' => "No Plan"
        ]);
        StripeProducts::insert([
            'stripe_id'=>env("STANDARD_MONTHLY_PLAN_ID"),
            'description' => "5 CAD/month"
        ]);
        StripeProducts::insert([
            'stripe_id'=>env("STANDARD_YEARLY_PLAN_ID"),
            'description' => "50 CAD/year"
        ]);
        StripeProducts::insert([
            'stripe_id'=>env("PREMIUM_MONTHLY_PLAN_ID"),
            'description' => "10 CAD/month"
        ]);
        StripeProducts::insert([
            'stripe_id'=>env("PREMIUM_YEARLY_PLAN_ID"),
            'description' => "100 CAD/year"
        ]);
    }
}
