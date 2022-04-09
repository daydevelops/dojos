<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ViewsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function the_view_count_for_a_dojo_can_increment() {
        
    }

    /** @test */
    public function the_view_count_does_not_increase_for_the_dojo_owner() {
        
    }

    /** @test */
    public function the_view_count_does_not_increase_for_the_admin() {
        
    }
}
