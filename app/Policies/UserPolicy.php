<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function update(User $authed_user, User $user)
    {
        return $authed_user->id == $user->id;
    }

    public function delete(User $authed_user, User $user)
    {
        return $authed_user->id == $user->id;
    }

  
}
