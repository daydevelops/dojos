<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dojo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\StripeProductSeeder;
use Tests\TestCase;

class DiscountsTest extends TestCase
{

    use DatabaseMigrations;

    protected function addProducts()
    {
        (new DatabaseSeeder())->call(StripeProductSeeder::class);
    }

    
    /** @test */
    public function an_admin_can_change_a_users_discount() {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin'=>1]);
        $this->signIn($admin);
        $this->assertEquals(0,$user->discount);
        $this->json('patch','/api/users/'.$user->id.'/discount',['discount'=>10]);
        $this->assertEquals(10,$user->fresh()->discount);
    }

    /** @test */
    public function an_admin_cannot_give_a_discount_to_a_non_existing_user() {
        $admin = User::factory()->create(['is_admin'=>1]);
        $this->signIn($admin);
        $this->json('patch','/api/users/99/discount',['discount'=>10])->assertStatus(404);
    }

    /** @test */
    public function a_user_cannot_be_given_a_discount_more_than_100_percent() {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin'=>1]);
        $this->signIn($admin);
        $this->assertEquals(0,$user->discount);
        $this->json('patch','/api/users/'.$user->id.'/discount',['discount'=>101])->assertStatus(422);
        $this->assertEquals(0,$user->fresh()->discount);
    }

    /** @test */
    public function a_user_cannot_be_given_a_discount_less_than_0_percent() {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin'=>1]);
        $this->signIn($admin);
        $this->assertEquals(0,$user->discount);
        $this->json('patch','/api/users/'.$user->id.'/discount',['discount'=>-1])->assertStatus(422);
        $this->assertEquals(0,$user->fresh()->discount);
    }

    /** @test */
    public function a_user_cannot_change_a_users_discount() {
        $this->signIn();
        $this->json('patch','/api/users/1/discount',['discount'=>10])->assertStatus(401);
    }
    
    /** @test */
    public function a_guest_cannot_change_a_users_discount() {
        $user = User::factory()->create();
        $this->json('patch','/api/users/1/discount',['discount'=>10])->assertStatus(401);
    }

    /** @test */
    public function a_users_discount_is_applied_when_viewing_plans() {
        $this->addProducts();
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin'=>1]);
        $this->signIn($admin);
        $this->json('patch','/api/users/'.$user->id.'/discount',['discount'=>10]);
        $this->signIn($user->fresh());
        $this->assertEquals(10,$user->fresh()->discount);
        $plans = $this->json('get', '/api/subscribe/plans')->original;
        $this->assertEquals(4.5,$plans[1]->price);
    }

    /** @test */
    public function a_user_is_charged_the_discounted_price() {
        
    }

}
