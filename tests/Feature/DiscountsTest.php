<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dojo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DiscountsTest extends TestCase
{

    use DatabaseMigrations;
    
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
        
    }

    /** @test */
    public function a_user_cannot_be_given_a_discount_more_than_100_percent() {
        
    }

    /** @test */
    public function a_user_cannot_be_given_a_discount_less_than_0_percent() {
        
    }

    /** @test */
    public function a_user_cannot_change_a_users_discount() {
        
    }

    /** @test */
    public function a_guest_cannot_change_a_users_discount() {
        
    }

    /** @test */
    public function a_user_sees_the_discounted_price_on_the_plans_page() {
        
    }

    /** @test */
    public function a_user_is_charged_the_discounted_price() {
        
    }

}
