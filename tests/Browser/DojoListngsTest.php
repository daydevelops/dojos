<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /** @test */
    public function a_guest_can_see_dojo_listings()
    {
        $dojo = Dojo::factory()->create();
        $this->browse(function (Browser $browser) use ($dojo) {
            $browser->visit('/')
                    ->assertSee($dojo->name);
        });
    }

    /** @test */
    public function a_guest_cannot_filter_by_owned_dojos() {
        
    }

    /** @test */
    public function a_user_can_filter_by_owned_dojos() {
        
    }

    /** @test */
    public function a_user_can_see_the_crud_btns_for_their_dojo() {
        
    }

    /** @test */
    public function a_user_cannot_see_the_crud_btns_for_a_dojo_they_do_not_own() {
        
    }

    /** @test */
    public function all_categories_are_available_in_the_filter() {
        
    }

    /** @test */
    public function category_all_shows_all_dojos() {
        
    }
}
