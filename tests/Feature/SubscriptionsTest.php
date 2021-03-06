<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use App\Models\Dojo;
use App\Models\StripeProduct;
use Laravel\Cashier\Subscription;
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


    //// PAYMENT AND DOJO INFORMATION /////

    /** @test */
    public function a_user_can_get_their_payments_intent()
    {
        $user = User::factory()->create();
        $this->signIn($user);
        $res = $this->get('/api/payments/getIntents');
        $this->assertInstanceOf('Stripe\SetupIntent', $res->original);
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
        $this->assertEquals($res->original['plan_id'], 2);
    }

    /** @test */
    public function a_user_cannot_see_the_subscription_for_a_dojo_they_do_not_own()
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
    public function a_guest_cannot_see_invoices() {
        $this->get('/api/payments/invoice')->assertStatus(302);
    }
    
    // /** @test */
    // public function a_user_can_see_a_list_of_invoices() {
    //     $data = $this->createSubscribedDojo();
    //     $invoices = $this->json('get','/api/payments/invoice')->original;
    //     $this->assertCount(1,$invoices);
    //     // $this->assertEquals()
    // }

    // /** @test */
    // public function a_user_can_download_an_invoice() {
        
    // }













    ///// DELETING SUBSCRIPTIONS /////

    /** @test */
    public function a_user_can_cancel_a_dojos_subscription()
    {
        // given a user has a standard plan
        $data = $this->createSubscribedDojo();
        $route = $this->getSubscribeRoute(1,'pm_card_visa',$data['dojo']);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('dojos', ['subscription_id' => null]);
        $this->assertDatabaseHas('subscriptions', ['stripe_status' => "canceled"]);
    }

    /** @test */
    public function a_user_unsubscribing_does_not_effect_other_users_subscription() {
        $data = $this->createSubscribedDojo();
        $data = $this->createSubscribedDojo();
        $this->assertDatabaseCount('dojos', 2);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('subscriptions', 2);
        $route = $this->getSubscribeRoute(1,'pm_card_visa',$data['dojo']);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 2);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('subscriptions', 2);
        $this->assertDatabaseHas('dojos', [
            'id' => 1,
            'subscription_id' => 1
        ]);
        $this->assertDatabaseHas('dojos', [
            'id' => 2,
            'subscription_id' => null
            ]);
        $this->assertDatabaseHas('subscriptions', ['stripe_status' => "canceled"]);
        $this->assertDatabaseHas('subscriptions', ['stripe_status' => "active"]);
    }

    /** @test */
    public function unsubscribing_the_last_dojo_on_a_subscription_cancels_it() {        
        $data = $this->createSubscribedDojo();
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('subscriptions', [
            'stripe_status' => "active",
            'quantity' => 1
        ]);
        // create a 2nd dojo on the same plan
        $user = User::first();
        $dojo = Dojo::factory()->create(['user_id'=>$user->id]);
        $route = $this->getSubscribeRoute(2,"pm_card_visa",$dojo);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 2);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', [
            'stripe_status' => "active",
            'quantity' => 2
        ]);
        // remove first dojo, should decrease quantity
        $route = $this->getSubscribeRoute(1,'pm_card_visa',$data['dojo']);
        $this->get($route);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', [
            'stripe_status' => "active",
            'quantity' => 1
        ]);
        // remove second dojo, should cancel plan
        $route = $this->getSubscribeRoute(1,'pm_card_visa',$dojo);
        $this->get($route);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', [
            'stripe_status' => "canceled",
            'quantity' => 1
        ]);
    }

    /** @test */
    public function subscription_is_updated_when_a_dojo_is_deleted()
    {
        $data = $this->createSubscribedDojo();
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('subscriptions', [
            'stripe_status' => "active",
            'quantity' => 1
        ]);
        // create a 2nd dojo on the same plan
        $user = User::first();
        $dojo = Dojo::factory()->create(['user_id'=>$user->id]);
        $route = $this->getSubscribeRoute(2,"pm_card_visa",$dojo);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 2);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', [
            'stripe_status' => "active",
            'quantity' => 2
        ]);
        // delete  and unsubscribe the first dojo
        $this->json('delete', '/api/dojos/' . $data['dojo']['id']);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', [
            'stripe_status' => "active",
            'quantity' => 1
        ]);
    }

    /** @test */
    public function a_users_subscriptions_are_deleted_if_they_delete_their_account()
    {
        $data = $this->createSubscribedDojo();
        $this->json('delete', '/api/users/' . $data['user']['id']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('dojos', 0);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('subscriptions', ['stripe_status' => "canceled"]);
    }

    /** @test */
    public function a_user_can_select_the_free_plan_if_they_are_not_activated()
    {
        $data = $this->createSubscribedDojo();
        auth()->user()->update(['is_active' => 0]);
        $route = $this->getSubscribeRoute(1,'pm_card_visa',$data['dojo']);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('dojos', ['subscription_id' => null]);
        $this->assertDatabaseHas('subscriptions', ['stripe_status' => "canceled"]);
    }













    ///// CREATING SUBSCRIPTIONS /////

    /** @test */
    public function a_user_can_subscribe_a_dojo_they_own()
    {
        $payment_methods = [
            'pm_card_visa',
            'pm_card_visa_debit',
            'pm_card_mastercard',
            'pm_card_mastercard_debit',
            'pm_card_mastercard_prepaid',
            'pm_card_amex'
        ];

        foreach ($payment_methods as $pm) {
            $this->runDatabaseMigrations();
            $this->testSubscribedDojo($pm);
        }
    }

    // assert a user can subscribe using a specified card
    protected function testSubscribedDojo($payment_method) {
        $this->assertDatabaseCount('dojos', 0);
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseCount('subscription_items', 0);
        $data = $this->createSubscribedDojo($payment_method);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('dojos', ['subscription_id' => 1]);
        $sp = StripeProduct::find(2);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $sp->product_id,
            'name' => $sp->description
        ]);
    }

    /** @test */
    public function a_user_cannot_subscribe_to_a_plan_if_they_are_not_activated()
    {
        // EXCEPT for selecting the free plan as in the test below
        $this->addProducts();
        $this->signIn(User::factory()->create(['is_active' => 0]));
        $dojo = Dojo::factory()->create();
        $route = $this->getSubscribeRoute(1,'pm_card_visa',$dojo);
        $this->get($route)->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_subscribe_a_dojo_they_do_not_own()
    {
        $this->addProducts();
        $dojo = Dojo::factory()->create();
        $user = User::factory()->create();
        $this->signIn($user->fresh());
        $route = $this->getSubscribeRoute(1,'pm_card_visa',$dojo);
        $res = $this->get($route)->assertStatus(403);
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseCount('subscription_items', 0);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseHas('dojos', ['subscription_id' => null]);
    }

    /** @test */
    public function a_user_can_subscribe_multiple_dojos()
    {
        // given the user has a subscribed dojo already
        $data = $this->createSubscribedDojo();
        // when they subscribe a new dojo
        $dojo = Dojo::factory()->create(['user_id' => auth()->id()]);
        $new_plan = StripeProduct::find(4);
        $route = $this->getSubscribeRoute(4,'pm_card_visa',$dojo);
        $this->get($route);
        // the user will have 2 subscription items under their subscription
        $this->assertDatabaseCount('dojos', 2);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 2);
        $this->assertDatabaseCount('subscription_items', 2);
        $this->assertDatabaseHas('dojos', ['subscription_id' => 2]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $new_plan->product_id,
            'name' => $new_plan->description
        ]);
    }

    /** @test */
    public function a_user_cannot_subscribe_unless_stripe_accepts_their_payment()
    {
        // payment methods that should fail
        $incomplete_pm = [
            'pm_card_chargeCustomerFail', // incomplete
            'pm_card_riskLevelHighest', // incomplete
            'pm_card_chargeDeclinedFraudulent', // incomplete
        ];
        
        $bad_pm = [
            'pm_card_chargeDeclinedExpiredCard', // card error
            'pm_card_cvcCheckFail', // card error
        ];

        foreach ($incomplete_pm as $pm) {
            $this->runDatabaseMigrations();
            // test a proper response when the user uses a bad card
            $this->addProducts();
            $dojo = Dojo::factory()->create();
            $user = User::first();
            $this->signIn($user); 
            $route = $this->getSubscribeRoute(2,$pm,$dojo);
            $this->get($route)->assertStatus(302);
            $this->assertDatabaseCount('dojos', 1);
            $this->assertDatabaseCount('subscriptions', 1);
            $this->assertDatabaseCount('subscription_items', 1);
            $this->assertDatabaseHas('dojos', ['subscription_id' => null]);
            $this->assertDatabaseHas('subscriptions', ['stripe_status' => "incomplete"]);
        }
        foreach ($bad_pm as $pm) {
            $this->runDatabaseMigrations();
            // test a proper response when the user uses a bad card
            $data = $this->createSubscribedDojo($pm);
            $this->assertDatabaseCount('dojos', 1);
            $this->assertDatabaseCount('subscriptions', 0);
            $this->assertDatabaseCount('subscription_items', 0);
            $this->assertDatabaseHas('dojos', ['subscription_id' => null]);
        }
    }

    /** @test */
    public function a_user_cannot_subscribe_to_a_plan_they_are_already_on() {
        $data = $this->createSubscribedDojo();
        $route = $this->getSubscribeRoute(2,'pm_card_visa',$data['dojo']);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('dojos', ['subscription_id' => 1]);
        $this->assertDatabaseHas('subscriptions', ['stripe_status' => "active"]);
    }


    ///// SWAPPING SUBSCRIPTIONS /////

    /** @test */
    public function a_user_can_swap_from_an_incomplete_payment_plan_to_another_plan() {
        $data = $this->createSubscribedDojo('pm_card_chargeCustomerFail');
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('dojos', ['subscription_id' => null]);
        $this->assertDatabaseHas('subscriptions', ['stripe_status' => "incomplete"]);
        $route = $this->getSubscribeRoute(2,'pm_card_visa',$data['dojo']);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('subscriptions', 2);
        $this->assertDatabaseCount('subscription_items', 2);
        $this->assertDatabaseHas('dojos', ['subscription_id' => 2]);
        $this->assertDatabaseHas('subscriptions', ['stripe_status' => "incomplete"]);
        $this->assertDatabaseHas('subscriptions', ['stripe_status' => "active"]);
    }

    /** @test */
    public function a_user_can_change_a_dojos_subscription()
    {
        // given a user has a standard plan
        $data = $this->createSubscribedDojo();
        $old_plan = StripeProduct::find(2);
        // when the user switched to a premium plan
        $new_plan = StripeProduct::find(4);
        $route = $this->getSubscribeRoute(4,'pm_card_visa',$data['dojo']);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 2);
        $this->assertDatabaseCount('subscription_items', 2);
        $this->assertDatabaseHas('dojos', ['subscription_id' => 2]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $new_plan->product_id,
            'name' => $new_plan->description,
            'stripe_status' => 'active',
            'quantity' => 1
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $old_plan->product_id,
            'name' => $old_plan->description,
            'stripe_status' => 'canceled',
            'quantity' => 1
        ]);
    }







    ///// COUPONS /////
    
    /** @test */
    public function a_user_can_subscribe_with_a_coupon() {
        $this->addProducts();
        $user = User::factory()->create(['coupon_id'=>1]);
        $dojo = Dojo::factory()->create(['user_id'=>1]);
        $this->signIn($user);
        $route = $this->getSubscribeRoute(2,"pm_card_visa",$dojo);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('dojos', ['subscription_id' => 1]);
        $sp = StripeProduct::find(2);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $sp->product_id,
            'name' => $sp->description . ": " . $user->coupon->description
        ]);
        $subscription = $user->fresh()->subscription($sp->description . ": " . $user->coupon->description);
        $this->assertEquals("CA$2.50",$subscription->latestPayment()->amount());
    }

    /** @test */
    public function subscribing_to_the_same_plan_but_with_a_coupon_creates_separate_subscriptions() {
        // given we have a subscribed dojo on plan 2
        // if we subscribe another dojo on plan 2 with a coupon
        // we should see 2 subscriptions

        $data = $this->createSubscribedDojo();
        $user = $data['user'];
        $user->update(['coupon_id'=>1]);
        $dojo = Dojo::factory()->create(['user_id'=>1]);
        $route = $this->getSubscribeRoute(2,"pm_card_visa",$dojo);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 2);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 2);
        $this->assertDatabaseCount('subscription_items', 2);
        $this->assertDatabaseHas('dojos', ['id'=>1, 'subscription_id' => 1]);
        $this->assertDatabaseHas('dojos', ['id'=>2, 'subscription_id' => 2]);
        $sp = StripeProduct::find(2);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $sp->product_id,
            'name' => $sp->description
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $sp->product_id,
            'name' => $sp->description . ": " . $user->coupon->description
        ]);
    }

    /** @test */
    public function cancelling_a_sub_on_a_coupon_plan_does_not_effect_the_non_coupon_sub() {
        // subscribe a dojo at full price
        $data = $this->createSubscribedDojo();

        // subsccribe a dojo with a coupon
        $user = $data['user'];
        $user->update(['coupon_id'=>1]);
        $dojo = Dojo::factory()->create(['user_id'=>1]);
        $route = $this->getSubscribeRoute(2,"pm_card_visa",$dojo);
        $this->get($route);

        // unsubscribe a dojo
        $route = $this->getSubscribeRoute(1,'pm_card_visa',$dojo);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 2);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 2);
        $this->assertDatabaseHas('dojos', [
            'id' => 1,
            'subscription_id' => 1
        ]);
        $this->assertDatabaseHas('dojos', [
            'id' => 2,
            'subscription_id' => null
        ]);
        $sp = StripeProduct::find(2);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $sp->product_id,
            'name' => $sp->description,
            'stripe_status' => "active"
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $sp->product_id,
            'name' => $sp->description . ": " . $user->coupon->description,
            'stripe_status' => "canceled"
        ]);

    }

    /** @test */
    public function a_user_can_have_multiple_subs_on_the_same_plan_coupon_combo() {
        $this->addProducts();
        $user = User::factory()->create(['coupon_id' => 1]);
        $this->signIn($user);
        $dojo = Dojo::factory()->create(['user_id'=>1]);
        $route = $this->getSubscribeRoute(2,"pm_card_visa",$dojo);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('subscriptions', [
            'stripe_status' => "active",
            'quantity' => 1
        ]);
        // create a 2nd dojo on the same plan
        $user = User::first();
        $dojo = Dojo::factory()->create(['user_id'=>$user->id]);
        $route = $this->getSubscribeRoute(2,"pm_card_visa",$dojo);
        $this->get($route);
        $this->assertDatabaseCount('dojos', 2);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', [
            'stripe_status' => "active",
            'quantity' => 2
        ]);
    }

    /** @test */
    public function a_user_can_cancel_a_subscription_and_resubscribe_with_a_coupon() {
        // subscribe a dojo at full price
        $data = $this->createSubscribedDojo();

        // give user a coupon
        $user = $data['user'];
        $user->update(['coupon_id'=>1]);

        // unsubscribe
        $dojo = $data['dojo'];
        $route = $this->getSubscribeRoute(1,"pm_card_visa",$dojo);
        $this->get($route);

        // make sure it was cancelled
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('dojos', [
            'id' => 1,
            'subscription_id' => null
        ]);
        $sp = StripeProduct::find(2);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $sp->product_id,
            'name' => $sp->description,
            'stripe_status' => "canceled"
        ]);
        
        // re subscribe with coupon
        $route = $this->getSubscribeRoute(2,"pm_card_visa",$dojo);
        $this->get($route);
        $this->assertDatabaseCount('subscriptions', 2);
        $this->assertDatabaseHas('dojos', [
            'id' => 1,
            'subscription_id' => 2
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => $sp->product_id,
            'name' => $sp->description . ": " . $user->coupon->description,
            'stripe_status' => "active"
        ]);
    }











    ///// WEBHOOK AND INCOMPLETE PAYMENTS /////

    /** @test */
    public function a_user_sees_payment_confirm_page_if_incomplete_payment() {
        $this->addProducts();
        $dojo = Dojo::factory()->create();
        $user = User::first();
        $this->signIn($user);
        $route = $this->getSubscribeRoute(2,'pm_card_chargeCustomerFail',$dojo);
        $this->get($route)->assertStatus(302);
    }

    /** @test */
    public function confirmed_payment_webhook_updates_the_dojo_and_plan() {
        $data = $this->createSubscribedDojo('pm_card_visa',1); // set up free plan
        // try to subscribe with a bad card
        $route = $this->getSubscribeRoute(2,'pm_card_chargeCustomerFail',$data['dojo']);
        $this->get($route)->assertStatus(302);
        // assume the user goes to the redirected page and completes the payment
        // mock the request to the webhook
        $subscription = DB::table('subscriptions')->where(['name'=>'dojo-1'])->get()[0];
        $mock_response = $this->getStripeWebhookMock($data['dojo']['id'],StripeProduct::find(2)->product_id,$subscription->stripe_id);
        $this->post('/api/payments/webhook',[
            'is_testing'=>1,
            'mock' => $mock_response
        ]);
        $this->assertDatabaseCount('dojos', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->assertDatabaseHas('dojos', ['subscription_id' => 1]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => 1,
            'stripe_plan' => StripeProduct::find(2)->product_id,
            'name' => "dojo-" . $data['dojo']['id'],
            'stripe_status' => 'active'
        ]);
    }

    // /** @test */
    // public function a_users_payment_method_is_saved_when_creating_a_subscription() {
    //     $this->addProducts();
    //     $dojo = Dojo::factory()->create();
    //     $user = User::first();
    //     $this->signIn($user);
    //     $route = $this->getSubscribeRoute(2,"pm_card_visa",$dojo,"1");
    //     $this->assertCount(0,$user->paymentMethods());
    //     $this->get($route);
    //     $this->assertCount(1,$user->paymentMethods());

    // }

    // /** @test */
    // public function a_users_payment_method_is_saved_when_updating_a_subscription() {
    //     // given a user has a standard plan
    //     $this->addProducts();
    //     $dojo = Dojo::factory()->create();
    //     $user = User::first();
    //     $this->signIn($user);
    //     $route = $this->getSubscribeRoute(2,"pm_card_visa",$dojo,"1");
    //     $this->get($route);
    //     $this->assertCount(1,$user->paymentMethods());
    //     // when the user switched to a new plan
    //     $route = $this->getSubscribeRoute(4,"pm_card_mastercard",$dojo,"1");
    //     $this->get($route);
    //     $this->assertCount(2,$user->paymentMethods());
    // }


}
