<?php

namespace Tests\Feature;

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
        $this->assertContains(User::find(1)->toArray(),$res);
        $this->assertContains(User::find(2)->toArray(),$res);
    }
    
    /** @test */
    public function users_list_page_is_not_accessable_to_non_admins() {
        $this->withExceptionHandling();
        $this->signIn();
        User::factory()->create();
        $this->get('/api/users')->assertstatus(401);
    }
}
