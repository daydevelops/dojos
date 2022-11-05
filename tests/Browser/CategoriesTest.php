<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CategoriesTest extends DuskTestCase
{

    use DatabaseMigrations;

    /** @test */
    public function everyone_can_see_the_categories_page() {
        $cats = Category::factory(3)->create();
        $this->browse(function (Browser $browser) use ($cats) {
            $browser->visit('/#/categories')
            ->waitFor('li.alert')
            ->assertSee('Available Categories')
            ->assertSee($cats[0]->name)
            ->assertSee($cats[1]->name)
            ->assertSee($cats[2]->name);
        });
    }

    /** @test */
    public function only_admin_can_see_category_delete_buttons() {
        $cats = Category::factory(4)->create();
        $admin = User::factory()->create(['is_admin'=>1]);
        $this->browse(function (Browser $browser) use ($admin) {
            $browser->visit('/#/categories')
            ->waitFor('li.alert')
            ->assertDontSee('i.fa-trash-alt')
            ->loginAs(User::factory()->create())
            ->visit('/#/categories')
            ->waitFor('li.alert')
            ->assertDontSee('i.fa-trash-alt')
            ->loginAs($admin)
            ->visit('/#/categories')
            ->waitFor('li.alert')
            ->assertVisible('i.fa-trash-alt')
            ->logout();
        });
    }

    /** @test */
    public function guests_cannot_see_the_add_category_form() {
        $this->browse(function (Browser $browser) {
            $browser->visit('/#/categories')
                  ->assertDontSee('input[name=name]')
                  ->loginAs(User::factory()->create())
                  ->visit('/#/categories')
                  ->assertVisible('input[name=name]')
                  ->logout();
        });
    }

    /** @test */
    public function added_category_is_appended_to_the_list() {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::factory()->create())
                ->visit('/#/categories')
                ->type('name','foobar')
                ->press('submit')
                ->waitFor('div.alert-flash') // wait for confirmation flash message
                ->assertInputValue('name',"")
                ->assertSee("foobar")
                ->logout();
        });
    }

    /** @test */
    public function user_sees_error_when_duplicationg_categories() {
        Category::factory()->create(['name'=>"foobar"]);
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::factory()->create())
                ->visit('/#/categories')
                ->type('name','foobar')
                ->press('submit')
                ->waitFor('span.help')
                ->assertSee("The name has already been taken")
                ->logout();
        });
    }

    /** @test */
    public function category_removed_from_list_when_deleted() {
        $cats[1] = Category::factory()->create();
        $cats[2] = Category::factory()->create();
        $this->browse(function (Browser $browser) use ($cats) {
            $browser->loginAs(User::factory()->create(['is_admin'=>1]))
                ->visit('/#/categories')
                ->waitFor('li.alert')
                ->assertSee($cats[2]->name)
                ->click('i.fa-trash-alt')
                ->waitFor('div.alert-flash') // wait for confirmation flash message
                ->assertDontSee($cats[2]->name)
                ->logout();
        });
    }
}
