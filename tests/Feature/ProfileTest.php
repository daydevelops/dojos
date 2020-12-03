<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    
    use DatabaseMigrations;

    /** @test */
    public function a_user_can_access_their_profile_information() {
        
    }

    /** @test */
    public function a_user_cannot_access_another_users_profile_information() {
        
    }

    /** @test */
    public function a_user_can_delete_their_account() {
        
    }

    /** @test */
    public function a_users_dojos_are_deleted_on_cascade() {
        
    }
}
