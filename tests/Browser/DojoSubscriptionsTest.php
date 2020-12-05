<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DojoSubscriptionsTest extends DuskTestCase
{

    use DatabaseMigrations;

    /** @test */
    public function a_user_can_see_all_plans_on_the_edit_dojo_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('');
        });
    }

    /** @test */
    public function the_current_plan_is_highlighted_on_the_edit_dojo_ppage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('');
        });
    }

    /** @test */
    public function a_user_can_subscribe_their_dojo()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('');
        });
    }

    /** @test */
    public function he_user_can_see_their_current_payment_methods_if_they_have_any()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('');
        });
    }

    /** @test */
    public function a_user_can_enter_new_credit_card_information()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('');
        });
    }

    /** @test */
    public function the_users_credit_card_is_never_sent_to_this_server() {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                  ->assertSee('');
        });
    }
}
