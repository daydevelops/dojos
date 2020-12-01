<?php

namespace Tests\Browser;

use App\Models\Dojo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class EditDojoTest extends DuskTestCase
{

    use DatabaseMigrations;

    /** @test */
    public function users_cannot_see_edit_page_for_a_dojo_they_do_not_own()
    {
        $dojo = Dojo::factory()->create();
        $this->browse(function (Browser $browser) use ($dojo) {
            $browser->loginAs(User::factory()->create())
                ->visit('/#/dojos/' . $dojo->id)
                ->pause(500)
                ->assertMissing('form.dojo-form');
        });
    }
}
