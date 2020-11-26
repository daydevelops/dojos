<?php

namespace Tests\Feature;

use App\Models\Dojo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AvatarTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function only_users_can_upload_avatars() {
        $this->post('api/avatar',[])->assertStatus(302);
    }

    /** @test */
	public function an_invalid_avatar_is_not_uploaded() {
        $this->signIn();
        $dojo = Dojo::factory()->create([
            'user_id' => auth()->id()
        ]);
		$this->json('post','/api/avatar',[
            'image' => 'not-an-image',
            'dojo_id' => $dojo->id
		])->assertStatus(422);
	}

	/** @test */
	public function a_user_cannot_upload_an_avatar_to_a_dojo_they_do_not_own() {
		$this->signIn();
        $dojo = Dojo::factory()->create();
		$avatar = UploadedFile::fake()->image('avatar.jpg');
		$this->json('post','/api/avatar',[
            'image' => $avatar,
            'dojo' => $dojo->id
		])->assertStatus(422);
    }
    
    /** @test */
    public function a_user_can_upload_an_avatar() {
		$this->signIn();
        $dojo = Dojo::factory()->create([
            'user_id' => auth()->id()
        ]);
		$avatar = UploadedFile::fake()->image('avatar.jpg');
		Storage::fake('public');
		$this->json('post','/api/avatar',[
			'image' => $avatar,
            'dojo' => $dojo->id
		])->assertStatus(200);
		Storage::disk('public')->assertExists('images/'.$avatar->hashName());
		$this->assertEquals('storage/images/'.$avatar->hashName(),Dojo::first()->image);
    }

    /** @test */
    public function admin_can_update_a_users_dojo_avatar() {
		$this->signIn(User::factory()->create(['is_admin'=>1]));
        $dojo = Dojo::factory()->create();
		$avatar = UploadedFile::fake()->image('avatar.jpg');
		Storage::fake('public');
		$this->json('post','/api/avatar',[
			'image' => $avatar,
            'dojo' => $dojo->id
		])->assertStatus(200);
		Storage::disk('public')->assertExists('images/'.$avatar->hashName());
		$this->assertEquals('storage/images/'.$avatar->hashName(),Dojo::first()->image);
    }

}
