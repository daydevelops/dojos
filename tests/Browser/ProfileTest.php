<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProfileTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function users_can_see_their_info_on_the_profile_page() {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();
            $browser->loginAs($user)
                ->visit('/#/profile/'.$user->id)
                ->pause(500)
                ->assertSee($user->name)
                ->assertSee($user->email)
                ->logout();
        });
    }

    /** @test */
    public function unauthorized_users_are_redirected_to_home() {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();
            $browser->loginAs(User::factory()->create())
                ->visit('/#/profile/'.$user->id)
                  ->pause(500)
                  ->assertPathIsNot(url('/')."/#/profile".$user->id);
        });
    }
}
