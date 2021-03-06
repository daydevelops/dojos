<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\UserDeactivated;
use App\Notifications\UserReactivated;
use App\Notifications\UserDeleted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index()
    {
        return User::all()->loadCount('dojos');
    }

    public function edit(Request $request, $user_id)
    {
        $user = User::where(['id' => $user_id])->select(['id', 'name', 'email'])->first();
        if (auth()->user()->can('update', $user)) {
            return $user;
        } else {
            return response('You Cannot Edit This User', 403);
        }
    }

    public function update(Request $request, User $user)
    {
        $data = request()->validate(['is_active' => 'required|boolean']);
        $user->update($data);
        if ($data['is_active']) {
            $user->notify(new UserReactivated());
        } else {
            $user->notify(new UserDeactivated());
        }
        return $user->fresh()->is_active;
    }

    public function destroy(Request $request, User $user)
    {
        if (auth()->user()->can('delete', $user)) {
            $user->notify(new UserDeleted());
            $user->delete();
            Auth::guard('web')->logout();
        } else {
            return response('You Cannot Delete This User', 403);
        }
    }

    public function discount(Request $request, User $user) {
        $data = request()->validate(['discount' => 'required|integer|max:100|min:0']);
        $user->update($data);
        return $user->fresh()->discount;
    }
}
