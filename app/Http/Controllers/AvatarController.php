<?php

namespace App\Http\Controllers;

use App\Models\Dojo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AvatarController extends Controller
{
    public function store(Request $request) {
        $data = request()->validate([
            'image' => 'required|image',
            'dojo' => [
                'required',
                'integer',
                Rule::exists('dojos','id')->where(function($query) {
                    $query->where('user_id',auth()->id());
                })
            ]
        ]);
        $dojo = Dojo::find($data['dojo']);
        $dojo->update([
            'image' => 'storage/' . request()->file('image')->store('images','public')
        ]);
        return $dojo->fresh()->image;
    }
}
