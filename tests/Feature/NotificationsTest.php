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

    // /** @test */
    // public function a_user_is_notified_when_they_update_a_dojos_subscription() {
    //     Notification::fake();
    //     // create a dojo subscribed to the 5 dollar plan
    //     $data = $this->triggerSubscriptionWebhook();
    //     Notification::assertSentTo(
    //         $data['user'], 
    //         function(DojoSubscriptionUpdated $notification) {
    //             return $notification->plan->description == "5 CAD/month";
    //         }
    //     );
    //     // subscribe the dojo to the free plan
    //     $route = $this->getSubscribeRoute(1,'pm_card_visa',$data['dojo']);
    //     $this->get($route);
    //     Notification::assertSentTo(
    //         $data['user'], 
    //         function(DojoSubscriptionUpdated $notification) {
    //             return $notification->plan->description == "No Plan";
    //         }
    //     );
        
    // }
}
