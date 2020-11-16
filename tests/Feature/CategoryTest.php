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
        Dojo::factory()->create();
        $this->assertInstanceOf(Dojo::class,Category::first()->dojos[0]);
    }
}
