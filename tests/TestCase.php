<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
	protected function setUp(): void {
		parent::setUp();
		// $this->withoutExceptionHandling();
	}

	protected function signIn($user=null) {
		$user = $user ?: User::factory('App\User')->create();
		$this->be($user);
		return $this;
	}

	protected function signout() {
		Auth::logout();
	}
}
