<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DojoCRUDTest extends TestCase
{

    use RefreshDatabase;

    // ADDING
    
    /** @test */
    public function a_user_can_create_a_dojo() {
        
    }

    /** @test */
    public function a_guest_cannot_create_a_dojo() {
        
    }

    // EDITING

    /** @test */
    public function a_user_can_edit_a_dojo() {
        
    }

    /** @test */
    public function an_admin_can_edit_a_dojo() {
        
    }

    /** @test */
    public function a_guest_cannot_edit_a_dojo() {
        
    }

    /** @test */
    public function a_user_can_only_edit_a_dojo_they_own() {
        
    }

    // DELETING

    /** @test */
    public function a_user_can_delete_their_dojo() {
        
    }

    /** @test */
    public function an_admin_can_delete_a_dojo() {
        
    }

    /** @test */
    public function a_guest_cannot_delete_a_dojo() {
        
    }

    /** @test */
    public function a_user_can_only_delete_a_dojo_they_own() {
        
    }

    // APPROVAL

    /** @test */
    public function a_dojo_must_be_approved_by_an_admin_before_being_public() {
        
    }

    /** @test */
    public function only_an_admin_can_approve_a_dojo() {
        
    }

    /** @test */
    public function a_dojo_cannot_have_an_unapproved_category() {
        
    }
}
