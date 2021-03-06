<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Dojo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\StripeProductSeeder;
use Tests\TestCase;

class CouponsTest extends TestCase
{

    use DatabaseMigrations;

    protected function addProducts()
    {
        (new DatabaseSeeder())->call(StripeProductSeeder::class);
    }

    
    /** @test */
    public function an_admin_can_change_a_users_coupon() {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin'=>1]);
        $this->signIn($admin);
        $this->addProducts();
        $this->assertEquals(null,$user->coupon_id);
        $this->json('patch','/api/users/'.$user->id.'/coupon',['coupon_id'=>1]);
        $this->assertEquals(50,$user->fresh()->coupon->discount);
    }

    /** @test */
    public function an_admin_cannot_give_a_coupon_to_a_non_existing_user() {
        $this->addProducts();
        $admin = User::factory()->create(['is_admin'=>1]);
        $this->signIn($admin);
        $this->json('patch','/api/users/99/coupon',['coupon_id'=>1])->assertStatus(404);
    }

    /** @test */
    public function a_user_cannot_change_a_users_coupon() {
        $this->addProducts();
        $this->signIn();
        $this->json('patch','/api/users/1/coupon',['coupon_id'=>1])->assertStatus(401);
    }
    
    /** @test */
    public function a_guest_cannot_change_a_users_coupon() {
        $this->addProducts();
        $user = User::factory()->create();
        $this->json('patch','/api/users/1/coupon',['coupon_id'=>1])->assertStatus(401);
    }

    /** @test */
    public function a_users_coupon_is_applied_when_viewing_plans() {
        $this->addProducts();
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin'=>1]);
        $this->signIn($admin);
        $this->json('patch','/api/users/'.$user->id.'/coupon',['coupon_id'=>1]);
        $this->signIn($user->fresh());
        $this->assertEquals(1,$user->fresh()->coupon_id);
        $plans = $this->json('get', '/api/subscribe/plans')->original;
        $this->assertEquals(2.5,$plans[1]->price);
    }

    /** @test */
    public function admin_can_see_a_list_of_coupons() {
        $this->addProducts();
        $user = User::factory()->create(['is_admin' => 1]);
        $this->signIn($user);
        $res = $this->get('/api/subscribe/coupons')->original;
        $this->assertInstanceOf(Coupon::class,$res->all()[0]);
    }

    /** @test */
    public function a_non_admin_cannot_see_a_list_of_coupons() {
        $user = User::factory()->create(['is_admin' => 0]);
        $this->signIn($user);
        $this->get('/api/subscribe/coupons')->assertStatus(401);
    }
}
