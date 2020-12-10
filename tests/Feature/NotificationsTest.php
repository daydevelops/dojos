<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NewUserRegistered;
use App\Notifications\UserDeactivated;
use App\Notifications\UserReactivated;
use App\Notifications\UserDeleted;
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
        
    }

    /** @test */
    public function a_user_is_notified_when_they_cancel_a_dojos_subscription() {
        
    }

    /** @test */
    public function a_user_is_notified_when_they_update_a_dojos_subscription() {
        
    }
}
