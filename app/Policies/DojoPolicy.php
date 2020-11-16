<?php

namespace App\Policies;

use App\Models\Dojo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DojoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Dojo  $dojo
     * @return mixed
     */
    public function view(User $user, Dojo $dojo)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Dojo  $dojo
     * @return mixed
     */
    public function update(User $user, Dojo $dojo)
    {
        return $dojo->user_id == auth()->id() || auth()->user()->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Dojo  $dojo
     * @return mixed
     */
    public function delete(User $user, Dojo $dojo)
    {
        return $dojo->user_id == auth()->id() || auth()->user()->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Dojo  $dojo
     * @return mixed
     */
    public function restore(User $user, Dojo $dojo)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Dojo  $dojo
     * @return mixed
     */
    public function forceDelete(User $user, Dojo $dojo)
    {
        //
    }
}
