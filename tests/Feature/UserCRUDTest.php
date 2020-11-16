<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserCRUDTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function an_admin_can_deactivate_a_user() {
        
    }

    /** @test */
    public function an_admin_can_access_a_list_of_users() {
        
    }
    
    /** @test */
    public function users_list_page_is_not_accessable_to_non_admins() {
        
    }
}
