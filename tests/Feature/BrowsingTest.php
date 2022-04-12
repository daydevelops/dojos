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
    public function the_public_dojo_list_is_filtered_properly() {

        /**
         * Testing the filtering algorithm in DojoController@index
         * 
         * Truth Table
         * has a subscription | user is activated | dojo is owned by me | is in phase 2 | Dojo is shown
         * ============================================================================================
         *         0          |         0         |          0          |       0       |       0
         *         0          |         0         |          1          |       0       |       1
         *         0          |         1         |          0          |       0       |       1
         *         0          |         1         |          1          |       0       |       1
         *         1          |         0         |          0          |       0       |       0
         *         1          |         1         |          0          |       0       |       1
         *         1          |         0         |          1          |       0       |       1
         *         1          |         1         |          1          |       0       |       1
         *         0          |         0         |          0          |       1       |       0
         *         0          |         0         |          1          |       1       |       1
         *         0          |         1         |          0          |       1       |       0
         *         0          |         1         |          1          |       1       |       1
         *         1          |         0         |          0          |       1       |       0
         *         1          |         0         |          1          |       1       |       1
         *         1          |         1         |          0          |       1       |       1
         *         1          |         1         |          1          |       1       |       1
         * 
         */
        
        config(['app.app_phase'=>'2']); 
        $this->assertEquals(config('app.app_phase'),2);

        $dojo_000 = Dojo::factory()->create();
        $dojo_000->user->update(['is_active'=>0]);
        $res = $this->get('/api/dojos')->json();
        $this->assertCount(0,$res);
        $dojo_000->delete();

        $dojo_001 = Dojo::factory()->create();
        $dojo_000->user->update(['is_active'=>0]);
        $this->signIn($dojo_001->user);
        $res = $this->get('/api/dojos')->json();
        $this->assertEquals($dojo_001->id,$res[0]['id']);
        $dojo_001->delete();

        $dojo_010 = Dojo::factory()->create();
        $res = $this->get('/api/dojos')->json();
        $this->assertCount(0,$res);
        $dojo_010->delete();

        $dojo_011 = Dojo::factory()->create();
        $this->signIn($dojo_011->user);
        $res = $this->get('/api/dojos')->json();
        $this->assertEquals($dojo_011->id,$res[0]['id']);
        $dojo_011->delete();

        $data = $this->createSubscribedDojo();
        $dojo_owner = User::find($data['user']['id']);
        
        $dojo_100 = $data['dojo'];
        $dojo_owner->update(['is_active'=>0]);
        $this->signIn(User::factory()->create());
        $res = $this->get('/api/dojos')->json();
        $this->assertCount(0,$res);

        $dojo_101 = $data['dojo'];
        $this->signIn($dojo_owner);
        $dojo_owner->update(['is_active'=>0]);
        $res = $this->get('/api/dojos')->json();
        $this->assertEquals($dojo_101->id,$res[0]['id']);

        $dojo_110 = $data['dojo'];
        $this->signIn(User::factory()->create());
        $dojo_owner->update(['is_active'=>1]);
        $res = $this->get('/api/dojos')->json();
        $this->assertEquals($dojo_110->id,$res[0]['id']);

        $dojo_111 = $data['dojo'];
        $this->signIn($dojo_owner);
        $dojo_owner->update(['is_active'=>1]);
        $res = $this->get('/api/dojos')->json();
        $this->assertEquals($dojo_110->id,$res[0]['id']);

        $dojo_111->delete();


        
        // TESTS FOR PHASE 1
        config(['app.app_phase'=>'1']); 
        $this->assertEquals(config('app.app_phase'),1);

        $dojo_000 = Dojo::factory()->create();
        $dojo_000->user->update(['is_active'=>0]);
        $res = $this->get('/api/dojos')->json();
        $this->assertCount(0,$res);
        $dojo_000->delete();

        $dojo_001 = Dojo::factory()->create();
        $dojo_000->user->update(['is_active'=>0]);
        $this->signIn($dojo_001->user);
        $res = $this->get('/api/dojos')->json();
        $this->assertEquals($dojo_001->id,$res[0]['id']);
        $dojo_001->delete();

        $dojo_010 = Dojo::factory()->create();
        $res = $this->get('/api/dojos')->json();
        $this->assertEquals($dojo_010->id,$res[0]['id']);
        $dojo_010->delete();

        $dojo_011 = Dojo::factory()->create();
        $this->signIn($dojo_001->user);
        $res = $this->get('/api/dojos')->json();
        $this->assertEquals($dojo_011->id,$res[0]['id']);
        $dojo_011->delete();

        $data = $this->createSubscribedDojo();
        $dojo_owner = User::find($data['user']['id']);
        
        $dojo_100 = $data['dojo'];
        $dojo_owner->update(['is_active'=>0]);
        $this->signIn(User::factory()->create());
        $res = $this->get('/api/dojos')->json();
        $this->assertCount(0,$res);

        $dojo_101 = $data['dojo'];
        $this->signIn($dojo_owner);
        $dojo_owner->update(['is_active'=>0]);
        $res = $this->get('/api/dojos')->json();
        $this->assertEquals($dojo_101->id,$res[0]['id']);

        $dojo_110 = $data['dojo'];
        $this->signIn(User::factory()->create());
        $dojo_owner->update(['is_active'=>1]);
        $res = $this->get('/api/dojos')->json();
        $this->assertEquals($dojo_110->id,$res[0]['id']);

        $dojo_111 = $data['dojo'];
        $this->signIn($dojo_owner);
        $dojo_owner->update(['is_active'=>1]);
        $res = $this->get('/api/dojos')->json();
        $this->assertEquals($dojo_110->id,$res[0]['id']);
    }

    /** @test */
    public function the_view_count_can_be_increased_for_a_dojo() {
        $dojo = Dojo::factory()->create();
        $this->signIn(User::factory()->create(['is_admin'=>true]));
        $this->assertEquals(0,$dojo->fresh()->views);
        $this->post('api/dojos/view/'.$dojo->id);
        $this->assertEquals(1,$dojo->fresh()->views);
    }

    /** @test */
    public function the_view_count_is_not_increased_for_a_dojo_if_viewed_by_the_owner() {
        $dojo = Dojo::factory()->create();
        $this->signIn($dojo->user);
        $this->assertEquals(0,$dojo->fresh()->views);
        $this->post('api/dojos/view/'.$dojo->id);
        $this->assertEquals(0,$dojo->fresh()->views);
    }
}
