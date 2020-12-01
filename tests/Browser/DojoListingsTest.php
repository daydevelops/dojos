<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\User;
use App\Models\Dojo;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DojoListingsTest extends DuskTestCase
{

    use DatabaseMigrations;

    /** @test */
    public function a_guest_can_see_dojo_listings()
    {
        $dojo = Dojo::factory()->create();
        $this->browse(function (Browser $browser) use ($dojo) {
            $browser->visit('/')
                ->waitFor('.card')
                ->assertSee($dojo->name);
        });
    }

    /** @test */
    public function a_guest_cannot_filter_by_owned_dojos()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertDontSee('Show My Dojos');
        });
    }

    /** @test */
    public function a_user_can_filter_by_owned_dojos()
    {
        $dojos = Dojo::factory(2)->create();
        $this->browse(function (Browser $browser) use ($dojos) {
            $browser->loginAs($dojos[0]->user_id)
                ->visit('/')
                ->assertSee('Show My Dojos')
                ->check('show_my_dojos')
                ->waitFor('.card')
                ->assertSee($dojos[0]->name)
                ->assertDontSee($dojos[1]->name)
                ->logout();
        });
    }

    /** @test */
    public function a_user_can_see_the_crud_btns_for_their_dojo()
    {
        $dojo = Dojo::factory()->create();
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::first())
                ->visit('/')
                ->waitFor('.card')
                ->assertVisible('i.fa-trash-alt')
                ->assertVisible('i.fa-edit')
                ->logout();
        });
    }

    /** @test */
    public function a_user_cannot_see_the_crud_btns_for_a_dojo_they_do_not_own()
    {
        $dojo = Dojo::factory()->create();
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::factory()->create())
                ->visit('/')
                ->waitFor('.card')
                ->assertDontSee('i.fa-trash-alt')
                ->assertDontSee('i.fa-edit')
                ->logout();
        });
    }

    /** @test */
    public function all_categories_are_available_in_the_filter()
    {

        Category::factory()->create(['name'=>'All']);
        Category::factory()->create(['name'=>'None']);
        $cats = Category::factory(3)->create();
        $this->browse(function (Browser $browser) use ($cats) {
            $browser->visit('/')
                ->waitFor('select.form-control>option')
                ->assertSee('All')
                ->assertSee('None')
                ->assertSee($cats[0]->name)
                ->assertSee($cats[1]->name)
                ->assertSee($cats[2]->name);
        });
    }

    /** @test */
    public function user_can_filter_by_category()
    {
        Category::factory()->create(['name'=>'All']);
        $dojos = Dojo::factory(2)->create();
        $this->browse(function (Browser $browser) use ($dojos) {
            $browser->visit('/')
                ->waitFor('select.form-control>option')
                ->select('category',$dojos[0]->category_id)
                ->assertSee($dojos[0]->name)
                ->assertDontSee($dojos[1]->name)
                ->select('category',1) // All
                ->assertSee($dojos[0]->name)
                ->assertSee($dojos[1]->name);
        });
    }

    /** @test */
    public function user_can_filter_by_category_and_my_dojos()
    {
        Category::factory()->create(['name'=>'All']);
        $user = User::factory()->create();
        $dojos = Dojo::factory(2)->create(['user_id'=>$user->id]);
        $dojos[2] = Dojo::factory()->create();
        $this->browse(function (Browser $browser) use ($dojos) {
            $browser->loginAs($dojos[0]->user_id)
                ->visit('/')
                ->waitFor('select.form-control>option')
                ->check('show_my_dojos')
                ->assertSee($dojos[0]->name)
                ->assertSee($dojos[1]->name)
                ->assertDontSee($dojos[2]->name)
                ->select('category',$dojos[0]->category_id)
                ->assertSee($dojos[0]->name)
                ->assertDontSee($dojos[1]->name)
                ->assertDontSee($dojos[2]->name)
                ->logout();
        });
    }
}
