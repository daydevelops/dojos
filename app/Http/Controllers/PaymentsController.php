<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
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
            'plan' => 'required|exists:stripe_products,product_id',
            'payment_method' => 'required'
        ]);
        $dojo = Dojo::find(request('dojo_id'));
        $user = auth()->user();
        $plan = StripeProduct::where(['product_id' => request('plan')])->first();

        // an inactive user cannot subscribe to a new plan, unless cancelling a current plan
        if (!$user->is_active && $plan->product_id != "free_plan") {
            return response('You cannot subscribe to this plan because your account has been deactivated.', 403);
        }

        // authorized user?
        if (!auth()->user()->can('update', $dojo)) {
            return response('You are not authorized to edit this dojo.', 403);
        }

        try {

            // load the currently subscribed plan, if there is one
            $current_subscription = $dojo->subscription;

            // my attempt at being verbose
            $is_on_free_plan = !$dojo->isSubscribed();
            $is_on_paid_plan = $dojo->isSubscribed();
            $wants_free_plan = $plan->product_id == "free_plan";
            $wants_paid_plan = $plan->product_id != "free_plan";
            $wants_different_paid_plan = ($current_subscription ? $current_subscription->stripe_plan : "free_plan") != $plan->product_id;
            
            if ($is_on_paid_plan && $wants_free_plan) {
                // user is switching to free plan, cancel their subscription
                // if the user is subscribed to the dojos current plan, which they should be
                $dojo->unsubscribe();
                // What happens when the quantity reaches zero?
                
            } else if ($is_on_paid_plan && $wants_different_paid_plan) {
                // user wants to switch to a new paid plan
                // if the user is subscribed to the dojos current plan, which they should be
                $dojo->unsubscribe();
                $dojo->subscribe($plan);

            } else if ($is_on_free_plan && $wants_paid_plan) {
                // user is moving from free plan to a paid plan
                $dojo->subscribe($plan);
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

    public function getIntents() {
        return auth()->user()->createSetupIntent();
    }

    public function getPaymentMethods() {
        $pms = auth()->user()->paymentMethods()->all();
        $results = array();
        foreach ($pms as $pm) {
            $card = $pm->card;
            $brand = $card->brand;
            $last4 = $card->last4;
            $id = $pm->id;
            array_push($results,compact('id','brand','last4'));
        }
        return $results;
    }

    public function plans() {
        $plans = StripeProduct::all();
        $discounts = [0];

        // get any personal discounts applied to the user
        if (auth()->check() && auth()->user()->coupon_id) {
            $coupon = auth()->user()->coupon;
            $discounts[] = $coupon->discount;
        }

        // get any global discounts
        $global_coupon = globalCoupon();
        if ($global_coupon) {
            $discounts[] = $global_coupon->discount;
        }

        foreach($plans as $plan) {
            $plan->price *= 1 - max($discounts) * 0.01;
        }

        return $plans;
    }

    public function coupons() {
        return Coupon::all();
    }

    public function invoice() {
        $invoices = auth()->user()->invoices();
        $result = array();
        forEach($invoices as $inv) {
            array_push($result,[
                'id' => $inv->id,
                'date' => $inv->date()->toFormattedDateString(),
                'total' => $inv->total()
            ]);
        }
        return $result;
    }

    public function downloadInvoice(Request $request, $id) {
        return auth()->user()->downloadInvoice($id, [
            'vendor' => 'Your Company',
            'product' => 'Your Product',
        ]);
    }
}
