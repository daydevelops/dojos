<?php

namespace App\Http\Controllers;

use App\Models\Dojo;
use Illuminate\Http\Request;

class DojoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'owner' => 'required|string|max:200',
            'url' => 'required|url',
            'location' => 'required|max:200',
            'price' => 'required|max:120',
            'category_id' => 'required|integer|exists:categories,id'
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
        //
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
        //
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
