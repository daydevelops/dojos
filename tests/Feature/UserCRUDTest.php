<?php

namespace Tests\Feature;

use App\Models\Dojo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserCRUDTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function an_admin_can_deactivate_a_user() {
        $this->signIn(User::factory()->create(['is_admin'=>true]));
        $user = User::factory()->create();
        $this->json('patch','/api/users/2',['is_active'=>false]);
        $this->assertEquals(0,$user->fresh()->is_active);
    }

    /** @test */
    public function an_admin_can_reactivate_a_user() {
        $this->signIn(User::factory()->create(['is_admin'=>true]));
        $user = User::factory()->create(['is_active'=>false]);
        $this->json('patch','/api/users/2',['is_active'=>true]);
        $this->assertEquals(1,$user->fresh()->is_active);
    }

    /** @test */
    public function an_admin_can_access_a_list_of_users() {
        $this->signIn(User::factory()->create(['is_admin'=>true]));
        User::factory(2)->create();
        $res = $this->get('/api/users')->json();
        $this->assertContains(User::find(1)->loadCount('dojos')->toArray(),$res);
        $this->assertContains(User::find(2)->loadCount('dojos')->toArray(),$res);
    }
    
    /** @test */
    public function users_list_page_is_not_accessable_to_non_admins() {
        $this->withExceptionHandling();
        $this->signIn();
        User::factory()->create();
        $this->get('/api/users')->assertstatus(401);
    }
    
    /** @test */
    public function a_user_can_access_their_profile_information() {
        $user = User::factory()->create();
        $this->signIn($user);
        $res = $this->get('/api/users/'.auth()->id())->json();
        $this->assertEquals($res,[
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    /** @test */
    public function a_user_cannot_access_another_users_profile_information() {
        $user = User::factory()->create();
        $this->signIn();
        $res = $this->get('/api/users/'.$user->id)->assertstatus(403);
    }

    /** @test */
    public function a_user_can_delete_their_account() {
        $user = User::factory()->create();
        $this->signIn($user);
        $this->assertDatabaseHas('users',['id'=>$user->id]);
        $res = $this->json('delete','/api/users/'.auth()->id());
        $this->assertDatabaseMissing('users',['id'=>$user->id]);
    }

    /** @test */
    public function a_user_can_only_delete_their_own_account() {
        $user = User::factory()->create();
        $this->signIn();
        $res = $this->json('delete','/api/users/'.$user->id)->assertStatus(403);
        $this->assertDatabaseHas('users',['id'=>$user->id]);
    }

    /** @test */
    public function a_users_dojos_are_deleted_on_cascade() {
        Dojo::factory(2)->create();
        $this->signIn(User::first());
        $this->assertDatabaseCount('dojos',2);
        $this->json('delete','/api/users/'.auth()->id());
        $this->assertDatabaseMissing('dojos',['id'=>1]);
        $this->assertDatabaseHas('dojos',['id'=>2]);
    }
}
