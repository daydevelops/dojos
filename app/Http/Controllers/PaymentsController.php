<?php

namespace App\Http\Controllers;

use App\Models\Dojo;
use App\Models\StripeProduct;
use App\Notifications\DojoSubscriptionUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Cashier\Exceptions\SubscriptionUpdateFailure;
use Stripe\Exception\CardException;

class PaymentsController extends Controller
{
    public function subscribe()
    {
        request()->validate([
            'dojo_id' => 'required|exists:dojos,id',
            'plan' => 'required|exists:stripe_products,stripe_id',
            'payment_method' => 'required'
        ]);
        $dojo = Dojo::find(request('dojo_id'));
        $user = auth()->user();
        $plan = StripeProduct::where(['stripe_id' => request('plan')])->first();

        // an inactive user cannot subscribe to a new plan, unless cancelling a current plan
        if (!$user->is_active && $plan->stripe_id != "free_plan") {
            return response('You cannot subscribe to this plan because your account has been deactivated.', 403);
        }

        // authorized user?
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
                $user->notify(new DojoSubscriptionUpdated(
                    $dojo, 
                    StripeProduct::where(['stripe_id' => "free_plan"])->first()
                ));
                
            } else if ($is_on_paid_plan && $wants_different_paid_plan) {
                // user wants to switch to a new paid plan
                $current_subscription->swap($plan->stripe_id);

            } else if ($wants_paid_plan && $is_on_free_plan) {
                // user is moving from free plan to a paid plan
                $subscription = $dojo->user
                    ->newSubscription("dojo-" . $dojo->id, $plan->stripe_id)
                    ->create(request('payment_method'),[],['metadata' => ['dojo_id' => $dojo->id]]);
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
                [$exception->payment->id, 'redirect' => url("/#/dojos/".$dojo->id)]
            );
        } catch (SubscriptionUpdateFailure $exception) {
            // redirect to cashiers payment confirmation page
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => url("/#/dojos/".$dojo->id)]
            );
        } catch (CardException $exception) {
            return array(
                "status" => false,
                "message" => "There was a problem with your card. Please check your information and try again"
            );
        }
        return redirect("/#/dojos/".$dojo->id);
    }

    /**
     * Stripe Webhook Endpoint
     * 
     * Stripe will hit this method when a subscription is successfully created or updated
     * This method will update the dojos information to reflect the new plan and notify the owner
     */
    public function subscriptionSuccess(Request $request) {
        // the user is redirected here if their payment confirmation has success

        if ($request['is_testing']) {
            $event = json_decode($request['mock']);
            $dojo_id = $event->data->object->metadata->dojo_id;
            $plan_id = $event->data->object->plan->id;
        } else {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            // Retrieve the request's body and parse it as JSON
            $input = @file_get_contents("php://input");
            $event_json = json_decode($input);
            $event = \Stripe\Event::retrieve($event_json->id);
            if(!isset($event)) {
                // error
                return false;
            }
            $dojo_id = $event->data->object->metadata['dojo_id'];
            $plan_id = $event->data->object->plan['id'];
        }

        // Handle the event
        if ($event->type == 'customer.subscription.created' || $event->type == 'customer.subscription.updated') {
            // update dojo information
            $dojo = Dojo::find($dojo_id);
            $subscription_id = DB::table('subscriptions')->where(['stripe_id'=>$event->data->object->id])->get()[0]->id;
            $dojo->update(['subscription_id' => $subscription_id]);
            $subscription = $dojo->subscription;
            $subscription->update(['stripe_status' => $event->data->object->status]);
            $user = $dojo->user;
            $plan_id = $plan_id;
            $user->notify(new DojoSubscriptionUpdated(
                $dojo, 
                StripeProduct::where(['stripe_id' => $plan_id])->first()
            ));
        } else {
            return 'Received unknown event type ' . $event->type;
        }

        http_response_code(200);
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
