<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dojo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BrowsingTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_guest_can_access_a_list_of_categories() {
        Category::factory(2)->create();
        $res = $this->get('/api/categories')->json();
        $this->assertContains(Category::find(1)->toArray(),$res);
        $this->assertContains(Category::find(2)->toArray(),$res);
    }

    /** @test */
    public function guests_cannot_access_unapproved_categories() {
        Category::factory()->create();
        Category::factory()->create(['approved'=>0]);
        $res = $this->get('/api/categories')->json();
        $this->assertContains(Category::find(1)->toArray(),$res);
        $this->assertNotContains(Category::find(2)->toArray(),$res);
    }

    /** @test */
    public function users_cannot_access_unapproved_categories() {
        $this->signIn();
        Category::factory()->create();
        Category::factory()->create(['approved'=>0]);
        $res = $this->get('/api/categories')->json();
        $this->assertContains(Category::find(1)->toArray(),$res);
        $this->assertNotContains(Category::find(2)->toArray(),$res);
    }

    /** @test */
    public function admin_can_see_a_list_of_unapproved_categories() {
        $this->signIn(User::factory()->create(['is_admin'=>true]));
        Category::factory(2)->create();
        $res = $this->get('/api/categories')->json();
        $this->assertContains(Category::find(1)->toArray(),$res);
        $this->assertContains(Category::find(2)->toArray(),$res);
    }

    /** @test */
    public function a_guest_can_see_all_dojos() {
        Dojo::factory(2)->create();
        $res = $this->get('/api/dojos')->json();
        $this->assertContains(Dojo::with('category')->find(1)->toArray(),$res);
        $this->assertContains(Dojo::with('category')->find(2)->toArray(),$res);
    }

    /** @test */
    public function a_dojo_is_provided_within_its_category() {
        $cat = Category::factory()->create();
        Dojo::factory(2)->create(['category_id'=>$cat->id]);
        $res = $this->get('/api/dojos/category/1')->json();
        $this->assertContains(Dojo::find(1)->toArray(),$res);
        $this->assertContains(Dojo::find(2)->toArray(),$res);
    }

    /** @test */
    public function a_dojo_is_not_provided_in_the_wrong_category() {
        $cat = Category::factory()->create();
        Dojo::factory()->create(['category_id'=>$cat->id]);
        Dojo::factory()->create();
        $res = $this->get('/api/dojos/category/1')->json();
        $this->assertContains(Dojo::find(1)->toArray(),$res);
        $this->assertNotContains(Dojo::find(2)->toArray(),$res);
    }

    /** @test */
    public function dojos_from_inactive_users_are_not_shown() {
        $user = User::factory()->create(['is_active'=>0]);
        Dojo::factory()->create();
        Dojo::factory()->create(['user_id'=>$user->id]);
        $res = $this->get('/api/dojos')->json();
        $this->assertContains(Dojo::with('category')->find(1)->toArray(),$res);
        $this->assertNotContains(Dojo::with('category')->find(2)->toArray(),$res);
    }

    /** @test */
    public function a_user_can_see_their_own_dojo_even_if_they_are_deactivated() {
        $user = User::factory()->create(['is_active'=>0]);
        $this->signIn($user);
        Dojo::factory()->create();
        Dojo::factory()->create(['user_id'=>$user->id]);
        $res = $this->get('/api/dojos')->json();
        $this->assertContains(Dojo::with('category')->find(1)->toArray(),$res);
        $this->assertContains(Dojo::with('category')->find(2)->toArray(),$res);
    }
}
