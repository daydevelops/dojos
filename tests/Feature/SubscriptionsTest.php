<?php

namespace Tests\Feature;

use App\Models\Dojo;
use App\Models\StripeProduct;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\StripeProductSeeder;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SubscriptionsTest extends TestCase
{

    use DatabaseMigrations;

    protected function addProducts()
    {
        (new DatabaseSeeder())->call(StripeProductSeeder::class);
    }

    protected function createSubscribedDojo($payment_method = 'pm_card_visa', $stripe_product_id = 2)
    {
        $this->addProducts();
        $dojo = Dojo::factory()->create();
        $user = User::first();
        $this->signIn($user);
        return [
            'response' => $this->post("/api/subscribe", [
                "plan" => StripeProduct::find($stripe_product_id)->stripe_id,
                "payment_method" => $payment_method,
                "dojo_id" => $dojo->id
            ]),
            'dojo' => $dojo,
            'user' => $user
        ];
    }

    /** @test */
    public function a_user_can_get_their_payments_intent()
    {
        $user = User::factory()->create();
        $this->signIn($user);
        $res = $this->get('/api/payments/getIntents');
        $this->assertInstanceOf('Stripe\SetupIntent', $res->original);
    }

    /** @test */
    public function a_user_cannot_subscribe_to_a_plan_if_they_are_not_activated()
    {
        $this->signIn(User::factory()->create(['is_active' => 0]));
        $this->post('/api/subscribe')->assertStatus(403);
    }

    /** @test */
    public function a_dojo_knows_its_subscription()
    {
        //given we have a subscribed user and dojo
        $data = $this->createSubscribedDojo();
        // we can see its stripe product
        $this->assertEquals($data['dojo']->fresh()->subscription, $data['user']->subscriptions()->first());
    }

    /** @test */
    public function a_user_can_request_the_dojos_plan()
    {
        $data = $this->createSubscribedDojo();
        $this->signIn($data['user']);
        $res = $this->json('get', '/api/dojos/' . $data['dojo']->id . '/plan');
        $this->assertEquals($res->original, 2);
    }

    /** @test */
    public function a_user_cannot_see_the_payment_plan_for_a_dojo_they_do_not_own()
    {
        Dojo::factory()->create(['subscription_id' => 1]);

        // test that the dojos subscription_id is hidden
        $dojos = $this->get('/api/dojos')->json();
        $this->assertArrayNotHasKey('subscription_id', $dojos[0]);

        // test that the user cannot ask for it directly
        $this->signIn(User::factory()->create());
        $this->json('get', '/api/dojos/1/plan')->assertStatus(403);
    }

    /** @test */
    public function anyone_can_see_a_list_of_all_plans()
    {
        $this->addProducts();
        $plans = $this->json('get', '/api/subscribe/plans')->original;
        $this->assertCount(5, $plans);
        foreach ($plans as $p) {
            $this->assertInstanceOf(StripeProduct::class, $p);
        }
    }

    /** @test */
    public function a_user_can_subscribe_a_dojo_they_own()
    {
        $this->assertDatabaseCount('dojos', 0);
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseCount('subscription_items', 0);
        $data = $this->createSubscribedDojo();
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('dojos', ['subscription_id' => 1]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => StripeProduct::find(2)->stripe_id
        ]);
    }

    /** @test */
    public function a_user_cannot_subscribe_a_dojo_they_do_not_own()
    {
        $this->addProducts();
        $dojo = Dojo::factory()->create();
        $user = User::factory()->create();
        $this->signIn($user->fresh());
        $res = $this->post("/api/subscribe", [
            "dojo_id" => $dojo->id
        ])->assertStatus(403);
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseCount('subscription_items', 0);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseHas('dojos', ['subscription_id' => null]);
    }

    /** @test */
    public function a_user_can_subscribe_multiple_dojos()
    {
        // given the user has a subscribed dojo already
        // when they subscribe a new dojo
        // the user will have 2 subscription items under their subscription
    }

    /** @test */
    public function a_user_can_change_a_dojos_subscription()
    {
        // given a user has a standard plan
        // when the user switched to a premium plan
        // the users subscription is updated and prorated
    }

    /** @test */
    public function a_user_can_cancel_a_dojos_subscription()
    { }

    /** @test */
    public function a_user_cannot_subscribe_unless_stripe_accepts_their_payment()
    { }

    /** @test */
    public function a_dojo_cannot_be_deleted_without_first_cancelling_its_subscription()
    { }
}
