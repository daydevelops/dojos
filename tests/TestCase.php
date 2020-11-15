<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

	protected function signIn($user=null) {
		$user = $user ?: User::factory('App\User')->create();
		$this->be($user);
		return $this;
	}

	protected function signout() {
		Auth::logout();
	}
}
