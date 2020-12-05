<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Stripe\SetupIntent;
use Tests\TestCase;

class SubscriptionsTest extends TestCase
{
    
    use DatabaseMigrations;

    /** @test */
    public function a_user_can_get_their_payments_intent() {
        $user = User::factory()->create();
        $this->signIn($user);
        $res = $this->get('/api/payments/getIntents');
        $this->assertInstanceOf('Stripe\SetupIntent',$res->original);
    }

    /** @test */
    public function a_user_cannot_subscribe_to_a_plan_if_they_are_not_activated() {
        $this->signIn(User::factory()->create(['is_active'=>0]));
        $this->post('/api/subscribe')->assertStatus(403);
    }
}
