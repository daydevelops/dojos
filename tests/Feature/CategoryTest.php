<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dojo;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_has_dojos() {
        $dojos = Dojo::factory(2)->create();
        $cats = Category::factory(2)->create();
        $dojos[0]->categories()->attach($cats->pluck('id')->toArray());
        $dojos[1]->categories()->attach($cats->pluck('id')->toArray());
        $this->assertInstanceOf(Dojo::class,$cats[0]->dojos[0]);
        $this->assertInstanceOf(Dojo::class,$cats[0]->dojos[1]);
        $this->assertInstanceOf(Dojo::class,$cats[1]->dojos[0]);
        $this->assertInstanceOf(Dojo::class,$cats[1]->dojos[1]);
    }
}
