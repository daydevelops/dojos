<?php

return [

    'plans' => [
        'standard' => [
            'monthly' => env('STANDARD_MONTHLY_PLAN_ID'),
            'yearly' => env('STANDARD_YEARLY_PLAN_ID')
        ],
        'premium' => [
            'monthly' => env('PREMIUM_MONTHLY_PLAN_ID'),
            'yearly' => env('PREMIUM_YEARLY_PLAN_ID')
        ]
    ],

    'discount' => [
        'fifteen' => env('FIFTEEN_OFF_COUPON_CODE'),
        'half' => env('HALF_OFF_COUPON_CODE'),
        'full' => env('FULL_OFF_COUPON_CODE'),
    ]

];
