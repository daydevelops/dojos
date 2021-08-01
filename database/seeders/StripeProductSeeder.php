<?php

namespace Database\Seeders;

use App\Models\StripeProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            'description' => "No Plan",
            'price' => 0,
            'cycle' => 'month'
        ]);
        StripeProduct::insert([
            'product_id' => env("STANDARD_MONTHLY_PLAN_ID"),
            'description' => "5 CAD/month",
            'price' => 5,
            'cycle' => 'month'
        ]);
        StripeProduct::insert([
            'product_id' => env("STANDARD_YEARLY_PLAN_ID"),
            'description' => "50 CAD/year",
            'price' => 50,
            'cycle' => 'year'
        ]);
        StripeProduct::insert([
            'product_id' => env("PREMIUM_MONTHLY_PLAN_ID"),
            'description' => "10 CAD/month",
            'price' => 10,
            'cycle' => 'month'
        ]);
        StripeProduct::insert([
            'product_id' => env("PREMIUM_YEARLY_PLAN_ID"),
            'description' => "100 CAD/year",
            'price' => 100,
            'cycle' => 'year'
        ]);

        // add coupons
        DB::table('coupons')->insert([
            'code' => env('HALF_OFF_COUPON_CODE'),
            'description' => '50% off',
            'discount' => 50
        ]);
        DB::table('coupons')->insert([
            'code' => env('FULL_OFF_COUPON_CODE'),
            'description' => '100% off',
            'discount' => 100
        ]);
        DB::table('coupons')->insert([
            'code' => env('FIFTEEN_OFF_COUPON_CODE'),
            'description' => '15% off',
            'discount' => 15
        ]);
    }
}
