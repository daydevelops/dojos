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

    public function sampleDojo($encode_location=false,$with_categories = true) {
        $data = [
            'name' => 'foobar',
            'price' => '99$/month',
            'classes' => 'Monday-Friday at 730pm-9pm',
            'contact' => 'Call me at my 1111111111',
            'location' => $encode_location ? json_encode(['test'=>123]) : ['test'=>123],
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, 
                sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
                Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris 
                nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in 
                reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.'
        ];
        if ($with_categories) {
            $data['categories'] = ["1"];
        }
        return $data;
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
    public function a_user_cannot_see_the_edit_page_for_a_dojo_they_do_not_own() {
        $this->signIn();
        Dojo::factory()->create();
        $this->json('get','/api/dojos/1')->assertStatus(403);
    }

    /** @test */
    public function a_user_can_edit_a_dojo() {
        $this->signIn();
        Category::factory(2)->create(['approved'=>1]);
        $dojo = Dojo::factory()->create(['user_id'=>auth()->id()]);
        $this->assertDatabaseMissing('dojos',$this->sampleDojo(true,false));
        $this->assertDatabaseMissing('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>1]);
        $this->assertDatabaseMissing('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>2]);
        $data = $this->sampleDojo();
        $data['categories'] = ["1","2"];
        $this->json('patch','/api/dojos/1',$data);
        $this->assertDatabaseHas('dojos',$this->sampleDojo(true,false));
        $this->assertDatabaseHas('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>1]);
        $this->assertDatabaseHas('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>2]);
    }
    
    /** @test */
    public function an_admin_can_edit_a_dojo() {
        $this->signIn(User::factory()->create(['is_admin'=>true]));
        Category::factory(2)->create(['approved'=>1]);
        $dojo = Dojo::factory()->create();
        $this->assertDatabaseMissing('dojos',$this->sampleDojo(true,false));
        $this->assertDatabaseMissing('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>1]);
        $this->assertDatabaseMissing('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>2]);
        $data = $this->sampleDojo();
        $data['categories'] = ["1","2"];
        $this->json('patch','/api/dojos/1',$data);
        $this->assertDatabaseHas('dojos',$this->sampleDojo(true,false));
        $this->assertDatabaseHas('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>1]);
        $this->assertDatabaseHas('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>2]);
    }

    /** @test */
    public function a_guest_cannot_edit_a_dojo() {
        $dojo = Dojo::factory()->create();
        $data = $this->sampleDojo();
        $data['categories'] = ["1","2"];
        $this->json('patch','/api/dojos/1',$data);
        $this->assertDatabaseMissing('dojos',$this->sampleDojo(true,false));
        $this->assertDatabaseMissing('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>1]);
        $this->assertDatabaseMissing('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>2]);
    }

    /** @test */
    public function a_user_can_only_edit_a_dojo_they_own() {
        $this->signIn(User::factory()->create());
        $dojo = Dojo::factory()->create();
        $this->assertDatabaseMissing('dojos',$this->sampleDojo(true,false));
        $this->assertDatabaseMissing('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>1]);
        $this->assertDatabaseMissing('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>2]);
        $data = $this->sampleDojo();
        $data['categories'] = ["1","2"];
        $this->json('patch','/api/dojos/1',$data);
        $this->assertDatabaseMissing('dojos',$this->sampleDojo(true,false));
        $this->assertDatabaseMissing('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>1]);
        $this->assertDatabaseMissing('category_dojo',["dojo_id"=>$dojo->id,"category_id"=>2]);
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
