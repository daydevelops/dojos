<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function subscribe()
    {
        $user = auth()->user();
        if ($user->is_active) {
            auth()->user()->newSubscription('standard_monthly', request('plan'))->create(request('payment_method'));
        } else {
            return response('You cannot subscribe to this plan because your account has been deactivated.', 403);
        }
    }
    
    public function getIntents()
    {
        return auth()->user()->createSetupIntent();
    }
}
