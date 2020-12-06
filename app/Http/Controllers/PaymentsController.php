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
        $dojo = Dojo::find(request('dojo_id'));
        $user = auth()->user();

        if (auth()->user()->can('update', $dojo)) {

            if ($user->is_active) {
                // subscribe the user
                $subscription = auth()->user()
                    ->newSubscription('standard_monthly', request('plan'))
                    ->create(request('payment_method'));

                // link the dojo to this subscription plan
                // if ($dojo->subscription_id == null) {
                // no subscription in place already
                $dojo->update(['subscription_id' => $subscription->id]);
                // } else {
                // switch from previous subscription to new
                // }
            } else {
                return response('You cannot subscribe to this plan because your account has been deactivated.', 403);
            }
        } else {
            return response('You are not authorized to edit this dojo.', 403);
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
