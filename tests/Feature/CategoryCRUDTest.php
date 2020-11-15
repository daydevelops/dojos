<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class CategoryCRUDTest extends TestCase
{

    use RefreshDatabase;

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
        $this->signIn(User::factory()->create(['is_admin'=>false]));
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
        
    }

    /** @test */
    public function a_non_admin_cannot_delete_a_category() {
        
    }

}
