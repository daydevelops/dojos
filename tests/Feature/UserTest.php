<?php

namespace Tests\Feature;

use App\Models\Dojo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_has_dojos() {
        Dojo::factory()->create();
        $this->assertInstanceOf(Dojo::class,User::first()->dojos[0]);
    }
}
