<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->check() && auth()->user()->is_admin) {
            return Category::all();
        } else {
            return Category::where(['approved'=>1])->get();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = request()->validate(['name' => 'required|unique:categories,name']);
        // allow all categories to be approved
        $data['approved'] = 1; // auth()->user()->is_admin;
        $cat = Category::create($data);
        return $cat;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, Category $category)
    {
        $category->update(['approved'=>1]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        
        if (auth()->user()->can('delete',$category)) {
            $none = Category::where(['name'=>'None'])->first();
            $category->dojos()->update(['category_id' => $none->id]);
            $category->delete();
        }
    }
}
