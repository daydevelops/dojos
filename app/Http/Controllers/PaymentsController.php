<?php

namespace App\Http\Controllers;

use App\Models\Dojo;
use App\Models\StripeProduct;
use App\Notifications\DojoSubscriptionUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Cashier\Exceptions\SubscriptionUpdateFailure;
use PHPUnit\Framework\Error\Error;
use Stripe\Exception\CardException;
use Stripe\Subscription;

class PaymentsController extends Controller
{
    public function subscribe()
    {
        // VALIDATION 

        $dojo = Dojo::find(request('dojo_id'));
        $user = auth()->user();
        $plan = StripeProduct::where(['stripe_id' => request('plan')])->first();

        // an inactive user cannot subscribe to a new plan, unless cancelling a current plan
        if (!$user->is_active && $plan->stripe_id != "free_plan") {
            return response('You cannot subscribe to this plan because your account has been deactivated.', 403);
        }

        if (!auth()->user()->can('update', $dojo)) {
            return response('You are not authorized to edit this dojo.', 403);
        }

        try {
            // if the user has an incomplete subscription and is trying to create a new one, delete old one first
            $incomplete_subscriptions = $dojo->getIncompleteSubscriptions();
            if (count($incomplete_subscriptions) > 0) {
                // cancel it
                $incomplete_subscriptions[0]->cancelNow();
            }

            //load the currently subscribed plan, if there is one
            $current_subscription = $dojo->subscription;

            // my attempt at being verbose
            $is_on_free_plan = !$dojo->isSubscribed();
            $is_on_paid_plan = $dojo->isSubscribed();
            $wants_free_plan = $plan->stripe_id == "free_plan";
            $wants_paid_plan = $plan->stripe_id != "free_plan";
            $wants_different_paid_plan = ($current_subscription ? $current_subscription->stripe_plan : "free_plan") != $plan->stripe_id;

            if ($is_on_paid_plan && $wants_free_plan) {
                // user is switching to free plan, cancel their subscription
                $current_subscription->cancelNow();
                $dojo->update(['subscription_id' => null]);

            } else if ($is_on_paid_plan && $wants_different_paid_plan) {
                // user wants to switch to a new paid plan
                $current_subscription->swap($plan->stripe_id);

            } else if ($wants_paid_plan && $is_on_free_plan) {
                // user is moving from free plan to a paid plan
                $subscription = auth()->user()
                    ->newSubscription("dojo-" . $dojo->id, $plan->stripe_id)
                    ->create(request('payment_method'));
                $dojo->update(['subscription_id' => $subscription->id]);

            } else {
                // the user is trying to subscribe to a plan they already have.
                return array(
                    "status" => false,
                    "message" => "You are already subscribed to this plan!"
                );
            }
        } catch (IncompletePayment $exception) {
            // redirect to cashiers payment confirmation page
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => '/']
            );
        } catch (SubscriptionUpdateFailure $exception) {
            // redirect to cashiers payment confirmation page
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => '/']
            );
        } catch (CardException $exception) {
            return array(
                "status" => false,
                "message" => "There was a problem with your card. Please check your information and try again"
            );
        }
        $user->notify(new DojoSubscriptionUpdated($dojo, $plan));
        return array(
            "status" => true,
            "message" => "Your subscription has been updated!"
        );
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
