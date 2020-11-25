<?php

namespace App\Http\Controllers;

use App\Models\Dojo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
        $old_image = $dojo->image;
        $dojo->update([
            'image' => 'storage/' . request()->file('image')->store('images','public')
        ]);
        File::delete(public_path($old_image));
        return $dojo->fresh()->image;
    }
}
