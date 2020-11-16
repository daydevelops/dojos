<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dojo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DojoCRUDTest extends TestCase
{

    use DatabaseMigrations;

    public function sampleDojo($cat_id=1) {
        return [
            'name' => 'foobar',
            'category_id' => $cat_id,
            'url' => 'https://someWebsite.com',
            'location' => '123 main st. Planet Mars',
            'price' => '99$/month',
            'owner' => 'Mr. Bar'
        ];
    }

    // ADDING
    
    /** @test */
    public function a_user_can_create_a_dojo() {
        $this->withExceptionHandling();
        $this->signIn();
        Category::factory()->create(['approved'=>1]);
        $this->assertDatabaseCount('dojos',0);
        $this->post('/api/dojos',$this->sampleDojo());
        $this->assertDatabaseHas('dojos',['name'=>'foobar']);
    }

    /** @test */
    public function a_guest_cannot_create_a_dojo() {
        Category::factory()->create(['approved'=>1]);
        $this->assertDatabaseCount('dojos',0);
        $this->post('/api/dojos',$this->sampleDojo());
        $this->assertDatabaseMissing('dojos',['name'=>'foobar']);
    }

    /** @test */
    public function a_dojo_must_have_an_existing_category() {
        $this->signIn();
        $this->post('/api/dojos',$this->sampleDojo());
        $this->assertDatabaseMissing('dojos',['name'=>'foobar']);
    }

    /** @test */
    public function a_dojo_cannot_have_an_unapproved_category() {
        Category::factory()->create(['approved'=>0]);
        $this->signIn();
        $this->post('/api/dojos',$this->sampleDojo());
        $this->assertDatabaseCount('dojos',0);
    }

    /** @test */
    public function dojos_must_have_unique_names() {
        $this->signIn();
        Category::factory()->create(['approved'=>1]);
        $this->assertDatabaseCount('dojos',0);
        $this->post('/api/dojos',$this->sampleDojo());
        $this->post('/api/dojos',$this->sampleDojo());
        $this->assertDatabaseCount('dojos',1);
    }

    // EDITING

    /** @test */
    public function a_user_can_edit_a_dojo() {
        $this->signIn();
        Dojo::factory()->create(['user_id'=>User::first()->id]);
        $this->assertDatabaseMissing('dojos',$this->sampleDojo());
        $this->json('patch','/api/dojos/1',$this->sampleDojo());
        $this->assertDatabaseHas('dojos',$this->sampleDojo());
    }

    /** @test */
    public function an_admin_can_edit_a_dojo() {
        $this->signIn(User::factory()->create(['is_admin'=>true]));
        Dojo::factory()->create();
        $this->assertDatabaseMissing('dojos',$this->sampleDojo());
        $this->json('patch','/api/dojos/1',$this->sampleDojo());
        $this->assertDatabaseHas('dojos',$this->sampleDojo());
    }

    /** @test */
    public function a_guest_cannot_edit_a_dojo() {
        Dojo::factory()->create();
        $this->json('patch','/api/dojos/1',$this->sampleDojo());
        $this->assertDatabaseMissing('dojos',$this->sampleDojo());
    }

    /** @test */
    public function a_user_can_only_edit_a_dojo_they_own() {
        $this->signIn(User::factory()->create());
        Dojo::factory()->create();
        $this->assertDatabaseMissing('dojos',$this->sampleDojo());
        $this->json('patch','/api/dojos/1',$this->sampleDojo());
        $this->assertDatabaseMissing('dojos',$this->sampleDojo());
    }

    /** @test */
    public function auth_users_can_browse_a_list_of_their_dojos_to_edit() {
        $this->signIn();
        Dojo::factory(3)->create(['user_id'=>auth()->id()]);
        $res = $this->get('/api/dojos/edit')->json();
        $this->assertContains(Dojo::find(1)->toArray(),$res);
        $this->assertContains(Dojo::find(2)->toArray(),$res);
        $this->assertContains(Dojo::find(3)->toArray(),$res);
    }

    /** @test */
    public function users_can_only_see_their_own_dojos_on_the_edit_page() {
        $this->signIn();
        Dojo::factory()->create(['user_id'=>auth()->id()]);
        Dojo::factory()->create();
        $res = $this->get('/api/dojos/edit')->json();
        $this->assertContains(Dojo::find(1)->toArray(),$res);
        $this->assertNotContains(Dojo::find(2)->toArray(),$res);
    }

    // DELETING

    /** @test */
    public function a_user_can_delete_their_dojo() {
        Dojo::factory()->create();
        $this->signIn(User::first());
        $this->assertDatabaseCount('dojos',1);
        $this->json('delete','/api/dojos/1');
        $this->assertDatabaseCount('dojos',0);
    }

    /** @test */
    public function an_admin_can_delete_a_dojo() {
        Dojo::factory()->create();
        $this->signIn(User::factory()->create(['is_admin'=>1]));
        $this->assertDatabaseCount('dojos',1);
        $this->json('delete','/api/dojos/1');
        $this->assertDatabaseCount('dojos',0);
    }

    /** @test */
    public function a_guest_cannot_delete_a_dojo() {
        Dojo::factory()->create();
        $this->assertDatabaseCount('dojos',1);
        $this->json('delete','/api/dojos/1');
        $this->assertDatabaseCount('dojos',1);
    }

    /** @test */
    public function a_user_can_only_delete_a_dojo_they_own() {
        Dojo::factory()->create();
        $this->signIn();
        $this->assertDatabaseCount('dojos',1);
        $this->json('delete','/api/dojos/1');
        $this->assertDatabaseCount('dojos',1);
    }
}
