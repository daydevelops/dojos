<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Dojo;
use App\Models\StripeProduct;
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
        config(['app.app_phase'=>'1']); 
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

    /** @test */
    public function a_users_coupon_is_applied_to_their_payment() {
        config(['app.app_phase'=>'1']); // apply a 15% off coupon
        $this->addProducts();
        $dojo = Dojo::factory()->create();
        $me = $dojo->user;
        $this->signIn($me);

        // subscribe to a plan
        $plan_id = 2;
        
        $new_plan = StripeProduct::find($plan_id);
        $this->get($this->getSubscribeRoute($plan_id,'pm_card_visa',$dojo));

        // assert the coupon is applied
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('dojos', [
            'subscription_id' => 1,
            'cost' => 4.25
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $new_plan->product_id,
            'name' => $new_plan->description . ": 15% off"
        ]);
        
    }

    /** @test */
    public function if_a_user_with_a_current_discount_subscribes_in_phase_1_their_largest_discount_takes_effect() {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin'=>1]);
        $this->signIn($admin);
        $this->addProducts();
        $this->assertEquals(null,$user->coupon_id);
        $this->json('patch','/api/users/'.$user->id.'/coupon',['coupon_id'=>1]);
        $this->assertEquals(50,$user->fresh()->coupon->discount);

        config(['app.app_phase'=>'1']); // apply a 15% off coupon

        $dojo = Dojo::factory()->create(['user_id' => $user->id]);

        // subscribe to a plan
        $plan_id = 2;
        
        $new_plan = StripeProduct::find($plan_id);
        $this->get($this->getSubscribeRoute($plan_id,'pm_card_visa',$dojo));

        // assert the 50% coupon is applied
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('dojos', [
            'subscription_id' => 1,
            'cost' => 2.5
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $new_plan->product_id,
            'name' => $new_plan->description . ": 50% off"
        ]);
    }

    /** @test */
    public function a_user_subscribing_in_phase_2_has_to_pay_full_price_by_default() {
        config(['app.app_phase'=>'2']); // no coupon
        $this->addProducts();
        $dojo = Dojo::factory()->create();
        $me = $dojo->user;
        $this->signIn($me);

        // subscribe to a plan
        $plan_id = 2;

        $this->assertNull($me->fresh()->highestCoupon());
        
        $new_plan = StripeProduct::find($plan_id);
        $this->get($this->getSubscribeRoute($plan_id,'pm_card_visa',$dojo));

        // assert the coupon is applied
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('dojos', [
            'subscription_id' => 1,
            'cost' => 5
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $new_plan->product_id,
            'name' => $new_plan->description
        ]);
    }

    /** @test */
    public function a_phase_1_discount_is_applied_when_viewing_plans() {
        config(['app.app_phase'=>'1']); 
        $this->addProducts();
        $this->signIn();
        $plans = $this->json('get', '/api/subscribe/plans')->original;
        $this->assertEquals(5*0.85,$plans[1]->price);
    }

    /** @test */
    public function the_global_coupon_helper_knows_of_global_coupons() {
        $this->addProducts();
        config(['app.app_phase'=>'0']); 
        $this->assertEquals(null,globalCoupon());
        config(['app.app_phase'=>'1']); 
        $this->assertEquals('15% off',globalCoupon()->description);
        config(['app.app_phase'=>'2']); 
        $this->assertEquals(null,globalCoupon());
    }
}
