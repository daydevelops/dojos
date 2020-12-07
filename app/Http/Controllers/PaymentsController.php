<?php

namespace App\Http\Controllers;

use App\Models\Dojo;
use App\Models\StripeProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentsController extends Controller
{
    public function subscribe()
    {
        // VALIDATION 

        $dojo = Dojo::find(request('dojo_id'));
        $user = auth()->user();
        $plan = StripeProduct::where(['stripe_id' => request('plan')])->first();

        // an inactive user cannot subscribe to a new plan, unless cancelling a current plan
        if ($user->is_active || $plan->stripe_id == "free_plan") {
            if (auth()->user()->can('update', $dojo)) {
                if ($dojo->isSubscribed()) {
                    $dojo->swapPlans($plan);
                } else {
                    $dojo->newPlan($plan);
                }
            } else {
                return response('You are not authorized to edit this dojo.', 403);
            }
        } else {
            return response('You cannot subscribe to this plan because your account has been deactivated.', 403);
        }
    }

    public function getIntents()
    {
        return auth()->user()->createSetupIntent();
    }

    public function plans()
    {
        return StripeProduct::all();
    }
}
