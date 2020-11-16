<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dojo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;


class CategoryCRUDTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function an_admin_can_add_a_category() {
        $this->signIn(User::factory()->create(['is_admin'=>true]));
        $data = ['name' => 'FooBar'];
        $this->assertDatabaseMissing('categories',$data);
        $this->post('/api/categories',$data);
        $data['approved'] = 1;
        $this->assertDatabaseHas('categories',$data);
    }

    /** @test */
    public function a_user_can_suggest_a_category() {
        $this->signIn();
        $data = ['name' => 'FooBar'];
        $this->assertDatabaseMissing('categories',$data);
        $this->post('/api/categories',$data);
        $data['approved'] = 0;
        $this->assertDatabaseHas('categories',$data);
    }

    /** @test */
    public function a_guest_cannot_suggest_a_category() {
        $data = ['name' => 'FooBar'];
        $this->post('/api/categories',$data)->assertStatus(302);
        $this->assertDatabaseMissing('categories',$data);
    }

    /** @test */
    public function an_admin_can_delete_a_category() {
        $this->signIn(User::factory()->create(['is_admin'=>true]));
        $none = Category::factory()->create(['name'=>'none']);
        Category::factory()->create();
        $this->assertDatabaseCount('categories',2);
        $this->json('delete','/api/categories/2');
        $this->assertDatabaseCount('categories',1);
    }

    /** @test */
    public function a_non_admin_cannot_delete_a_category() {
        $this->signIn();
        $none = Category::factory()->create(['name'=>'none']);
        Category::factory()->create();
        $this->assertDatabaseCount('categories',2);
        $this->json('delete','/api/categories/2');
        $this->assertDatabaseCount('categories',2);
    }

    /** @test */
    public function dojo_category_is_set_to_none_if_its_category_is_deleted() {
        $this->signIn(User::factory()->create(['is_admin'=>true]));
        $none = Category::factory()->create(['name'=>'none']);
        $cat = Category::factory()->create();
        Dojo::factory(3)->create(['category_id'=>$cat->id]);
        $this->assertDatabaseHas('dojos',['category_id'=>$cat->id]);
        $this->json('delete','/api/categories/2');
        $this->assertDatabaseMissing('dojos',['category_id'=>$cat->id]);
        $this->assertDatabaseHas('dojos',['category_id'=>$none->id]);
    }

    /** @test */
    public function an_admin_can_approve_a_category() {
        $this->signIn(User::factory()->create(['is_admin'=>true]));
        Category::factory()->create(['approved'=>0]);
        $this->json('patch','/api/categories/1/approve');
        $this->assertDatabaseHas('categories',['approved'=>1]);
    }

    /** @test */
    public function only_an_admin_can_approve_a_category() {
        $this->signIn();
        Category::factory()->create(['approved'=>0]);
        $this->json('patch','/api/categories/1/approve')->assertStatus(401);
        $this->assertDatabaseHas('categories',['approved'=>0]);
    }

    /** @test */
    public function category_names_must_be_unique() {
        $this->signIn();
        $data = ['name' => 'FooBar'];
        $this->post('/api/categories',$data);
        $this->post('/api/categories',$data);
        $this->assertDatabaseCount('categories',1);
    }

}
