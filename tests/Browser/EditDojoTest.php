<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Dojo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
                ->assertMissing('form.dojo-form')
                ->logout();
        });
    }

    /** @test */
    public function the_form_is_prefilled_with_the_dojo_info() {
        $dojo = Dojo::factory()->create();
        $this->browse(function (Browser $browser) use ($dojo){
            $browser->loginAs($dojo->user_id)
                ->visit('/#/dojos/' . $dojo->id)
                ->pause(500)
                ->assertInputValue('name',$dojo->name)
                ->assertInputValue('description',$dojo->description)
                ->assertInputValue('price',$dojo->price)
                ->assertInputValue('classes',$dojo->classes)
                ->assertInputValue('location',$dojo->location)
                ->assertInputValue('contact',$dojo->contact)
                ->logout();
        });
    }

    /** @test */
    public function all_catgeories_are_available()
    {
        $this->browse(function (Browser $browser) {
            $cats = Category::factory(3)->create();
            $dojo = Dojo::factory()->create();
            $browser->loginAs($dojo->user_id)
                ->visit('/#/dojos/' . $dojo->id)
                ->waitFor('select.form-control>option')
                ->assertSee($cats[0]->name)
                ->assertSee($cats[1]->name)
                ->assertSee($cats[2]->name)
                ->logout();
        });
    }

    /** @test */
    public function the_avatar_form_is_visible()
    {
        $this->browse(function (Browser $browser) {
            $dojo = Dojo::factory()->create()->fresh();
            $browser->loginAs($dojo->user_id)
                ->visit('/#/dojos/' . $dojo->id)
                ->waitFor('select.form-control>option')
                ->assertVisible('input#new-dojo-img')
                ->assertAttribute('img#dojo-image','src',asset($dojo->image))
                ->logout();
        });
    }

    /** @test */
    public function the_dojo_is_saved_upon_form_submission()
    {
        $dojo = Dojo::factory()->create();
        $this->browse(function (Browser $browser) use ($dojo) {
            $browser->loginAs($dojo->user_id)
                ->visit('/#/dojos/' . $dojo->id)
                ->clear('name')
                ->clear('description')
                ->clear('price')
                ->clear('classes')
                ->clear('location')
                ->clear('contact')
                ->type('name', 'foobar1')
                ->type('description', 'foobar2')
                ->type('price', 'foobar3')
                ->type('classes', 'foobar4')
                ->type('location', 'foobar5')
                ->type('contact', 'foobar6')
                ->press("#updatedojo")
                ->waitFor('div.alert-flash') // wait for confirmation flash message
                ->logout();
            $this->assertDatabaseHas('dojos',[
                'name' => 'foobar1',
                'description' => 'foobar2',
                'price' => 'foobar3',
                'classes' => 'foobar4',
                'location' => 'foobar5',
                'contact' => 'foobar6'
            ]);
        });
    }

    /** @test */
    public function a_new_avatar_can_be_uploaded()
    {
        $this->browse(function (Browser $browser) {
            $dojo = Dojo::factory()->create()->fresh();
            $avatar = UploadedFile::fake()->image('avatar.jpg');
            // Storage::fake('public');
            $browser->loginAs($dojo->user_id)
                ->visit('/#/dojos/' . $dojo->id)
                ->attach('input#new-dojo-img',$avatar)
                ->press('#uploadavatar')
                ->waitFor('div.alert-flash') // wait for confirmation flash message
                ->assertAttribute('img#dojo-image','src',asset($dojo->fresh()->image))
                ->logout();
		    Storage::disk('public')->assertExists(substr($dojo->fresh()->image,8)); // cut 'storage/' out of path
        });
    }
}
