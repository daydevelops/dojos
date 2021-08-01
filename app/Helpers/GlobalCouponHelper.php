<?php

use App\Models\Coupon;

if (! function_exists('globalCoupon')) {
    function globalCoupon() {
        // get any global discounts
        if (config('app.app_phase') == 1) {
            return Coupon::where(['description'=>'15% off'])->first();
        }

        return null;
    }
}