<?php

namespace App\Http\Controllers;

use App\Models\Dojo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DojoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->check() && auth()->user()->is_admin) {
            return Dojo::all();
        } else {
            // dont show dojos that belong to a deactivated user
            return Dojo::whereHas('user',function($q) {
                $q->where(['is_active'=>1]);
            })->get();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = request()->validate([
            'name' => 'required|string|max:200|unique:dojos,name',
            'description' => 'required|max:600',
            'location' => 'required|max:200',
            'classes' => 'required|max:200',
            'price' => 'required|max:120',
            'contact' => 'required|max:200',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories','id')->where(function($query) {
                    $query->where('approved',1);
                }),
            ]
        ]);

        $data['user_id'] = auth()->id();
        Dojo::create($data)->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dojo  $dojo
     * @return \Illuminate\Http\Response
     */
    public function show(Dojo $dojo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Dojo  $dojo
     * @return \Illuminate\Http\Response
     */
    public function edit(Dojo $dojo)
    {
        return $dojo;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Dojo  $dojo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Dojo $dojo)
    {
        $data = request()->validate([
            'name' => [
                'required',
                'string',
                'max:200',
                Rule::unique('dojos')->ignore($dojo->id),
            ],
            'description' => 'required|max:600',
            'location' => 'required|max:200',
            'classes' => 'required|max:200',
            'price' => 'required|max:120',
            'contact' => 'required|max:200',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories','id')->where(function($query) {
                    $query->where('approved',1);
                }),
            ]
        ]);
        if (auth()->user()->can('update',$dojo)) {
            $dojo->update($data);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Dojo  $dojo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dojo $dojo)
    {
        if (auth()->user()->can('delete',$dojo)) {
            $dojo->delete();
        }
    }
}
