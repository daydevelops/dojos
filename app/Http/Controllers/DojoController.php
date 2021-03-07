<?php

namespace App\Http\Controllers;

use App\Models\Dojo;
use App\Models\StripeProduct;
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
        $dojos = Dojo::with('category')->get()->all();

        // for guests or non admins, filter the dojos
        if (!auth()->check() || !auth()->user()->is_admin) {
            /**
             * A dojo should only be shown if:
             *      1. The auth user owns the dojo
             *      2. OR the dojo has a subscription AND the user is activated
             *      3. OR the app is not yet in phase 2 AND the user is activated
             */
            $user = auth()->check() ? auth()->user() : null;
            $result = array_values(array_filter($dojos, function($dojo) use ($user) {
                $owned_by_auth_user = $user ? $dojo->user_id == $user->id : false;
                $has_a_subscription = $dojo->isSubscribed();
                $owner_is_activated = $dojo->user->is_active;
                $not_yet_phase_2 = env('APP_PHASE') < 2; // in phase 2, users are limited by subscription type
                return $owned_by_auth_user || ($has_a_subscription && $owner_is_activated) || ($not_yet_phase_2 && $owner_is_activated);
            }));
        }

        return $result;
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
            'classes' => 'required|max:200',
            'price' => 'required|max:120',
            'contact' => 'required|max:200',
            'location' => 'required',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('approved', 1);
                }),
            ]
        ]);

        $data['location'] = json_encode($data['location']);
        $data['website'] = request('website');
        $data['facebook'] = request('facebook');
        $data['twitter'] = request('twitter');
        $data['youtube'] = request('youtube');
        $data['instagram'] = request('instagram');

        $data['user_id'] = auth()->id();
        Dojo::create($data)->save();
        return Dojo::where(['name'=>$data['name']])->get();
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
        if (auth()->user()->can('update', $dojo)) {
            return $dojo;
        } else {
            return response('You Cannot Edit This Dojo', 403);
        }
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
            'classes' => 'required|max:200',
            'price' => 'required|max:120',
            'contact' => 'required|max:200',
            'location' => 'required',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('approved', 1);
                }),
            ]
        ]);

        $data['location'] = json_encode($data['location']);
        $data['website'] = request('website');
        $data['facebook'] = request('facebook');
        $data['twitter'] = request('twitter');
        $data['youtube'] = request('youtube');
        $data['instagram'] = request('instagram');

        if (auth()->user()->can('update', $dojo)) {
            $dojo->update($data);
        } else {
            return response('You Cannot Edit This Dojo', 403);
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
        if (auth()->user()->can('delete', $dojo)) {
            $dojo->delete();
        } else {
            return response('You Cannot Edit This Dojo', 403);
        }
    }

    public function subscriptionPlan(Dojo $dojo) {
        // return the id of the payment plan for the dojo
        if (auth()->user()->can('update', $dojo)) {
            $subscription = $dojo->subscription;
            if ($subscription) {
                $stripe_plan = $subscription->stripe_plan;
                $plan_id = StripeProduct::where(['product_id'=>$stripe_plan])->first()->id;
            } else {
                // dojo does not have a plan yet, return id for free plan
                $plan_id = 1;
            }
            $cost = $dojo->cost;
            $cycle = $dojo->cycle;
            return compact('plan_id','cost','cycle');
        } else {
            return response("You Cannot See This Dojo's Subscription", 403);
        }
    }
}
