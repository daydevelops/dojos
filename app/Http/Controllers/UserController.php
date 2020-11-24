<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index() {
        return User::all()->loadCount('dojos');    
    }

    public function update(Request $request, User $user) {
       $data = request()->validate(['is_active'=>'required|boolean']); 
       $user->update($data);
       return $user->fresh()->is_active;
    }
}
