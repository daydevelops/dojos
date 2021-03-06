<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Dojo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_has_dojos() {
        Dojo::factory()->create();
        $this->assertInstanceOf(Dojo::class,User::first()->dojos[0]);
    }

    /** @test */
    public function it_has_a_coupon() {
        DB::table('coupons')->insert([
            'code' => env('HALF_OFF_COUPON_CODE'),
            'description' => '50% off',
            'discount' => 50
        ]);
        User::factory()->create(['coupon_id'=>1]);
        $this->assertInstanceOf(Coupon::class,User::first()->coupon);
    }
}
