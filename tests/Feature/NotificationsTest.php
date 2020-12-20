<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Dojo;
use App\Models\StripeProduct;
use App\Notifications\DojoSubscriptionUpdated;
use App\Notifications\NewUserRegistered;
use App\Notifications\UserDeactivated;
use App\Notifications\UserReactivated;
use App\Notifications\UserDeleted;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\StripeProductSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationsTest extends TestCase
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
    public function a_user_is_notified_when_they_register() {
        Notification::fake();
        $this->post('/register',[
            "name" => "Foobar",
            "email" => "foo@bar.com",
            "password" => "Foobarrr",
            "password_confirmation" => "Foobarrr"
        ]);
        Notification::assertSentTo(User::first(), NewUserRegistered::class);
    }

    /** @test */
    public function a_user_is_notified_when_they_are_deleted() {
        Notification::fake();
        $user = User::factory()->create();
        $this->signIn($user);
        $this->json('delete','/api/users/1');
        Notification::assertSentTo($user, UserDeleted::class);
    }

    /** @test */
    public function a_user_is_notified_when_they_are_deactivated() {
        Notification::fake();
        $user = User::factory()->create();
        $this->signIn(User::factory()->create(['is_admin'=>1]));
        $this->json('patch','/api/users/1',['is_active'=>0]);
        Notification::assertSentTo($user, UserDeactivated::class);
    }

    /** @test */
    public function a_user_is_notified_when_they_are_reactivated() {
        Notification::fake();
        $user = User::factory()->create(['is_active'=>0]);
        $this->signIn(User::factory()->create(['is_admin'=>1]));
        $this->json('patch','/api/users/1',['is_active'=>1]);
        Notification::assertSentTo($user, UserReactivated::class);
    }

    /** @test */
    public function a_user_is_notified_when_they_add_a_dojos_subscription() {
        Notification::fake();
        $data = $this->createSubscribedDojo();
        Notification::assertSentTo(
            $data['user'], 
            function(DojoSubscriptionUpdated $notification) {
                return $notification->plan->description == "5 CAD/month";
            }
        );
    }

    /** @test */
    public function a_user_is_notified_when_they_cancel_a_dojos_subscription() {
        Notification::fake();
        $data = $this->createSubscribedDojo();
        $this->post("/api/subscribe", [
            "plan" => StripeProduct::find(1)->stripe_id,
            "payment_method" => 'pm_card_visa',
            "dojo_id" => $data['dojo']['id']
        ]);
        Notification::assertSentTo(
            $data['user'], 
            function(DojoSubscriptionUpdated $notification) {
                return $notification->plan->description == "No Plan";
            }
        );
        
    }

    /** @test */
    public function a_user_is_notified_when_they_update_a_dojos_subscription() {
        Notification::fake();
        $data = $this->createSubscribedDojo();
        $new_plan = StripeProduct::find(4);
        $this->post("/api/subscribe", [
            "plan" => $new_plan->stripe_id,
            "payment_method" => 'pm_card_visa',
            "dojo_id" => $data['dojo']['id']
        ]);
        Notification::assertSentTo(
            $data['user'], 
            function(DojoSubscriptionUpdated $notification) {
                return $notification->plan->description == "10 CAD/month";
            }
        );
    }
}
