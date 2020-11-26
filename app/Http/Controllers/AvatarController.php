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
                'exists:dojos,id'
            ]
        ]);

        $dojo = Dojo::find($data['dojo']);

        if (auth()->user()->can('update', $dojo)) {
            // delete old file to keep disc clean
            $old_image = $dojo->image;
            if ($old_image != 'storage/images/default.png') { // dont delete default
                File::delete(public_path($old_image));
            }
    
            $dojo->update([
                'image' => 'storage/' . request()->file('image')->store('images','public')
            ]);
            return $dojo->fresh()->image;
        } else {
            return response()->json(['errors' => 'You cannot edit this dojo'],422);
        }
    }
}
