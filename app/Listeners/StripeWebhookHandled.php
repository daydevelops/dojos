<?php

namespace App\Listeners;

use App\Models\Dojo;
use Illuminate\Support\Facades\DB;
use App\Notifications\DojoSubscriptionUpdated;
use App\Models\StripeProduct;
use Laravel\Cashier\Events\WebhookHandled;

class StripeWebhookHandled
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  WebhookHandled  $event
     * @return void
     */
    public function handle(WebhookHandled $event)
    {

        // after stripe handles the webhook, we need to update the dojo information and notify the user 
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $event = $event->payload;

        // only listening for subscription events
        if (substr($event['type'],0,21) != 'customer.subscription') {
            return;
        }

        $data = $event['data'];

        $dojo_id = $data['object']['metadata']['dojo_id'];
        $plan_id = $data['object']['plan']['id'];
        $plan = StripeProduct::where(['product_id' => $plan_id])->first();
        $dojo = Dojo::find($dojo_id);
        if ($event['type'] == 'customer.subscription.deleted') {
            $subscription_id = null;
            $cost = null;
            $cycle = null;
        } else {
            $subscription_id = DB::table('subscriptions')->where(['stripe_id'=>$data['object']['id']])->get()[0]->id;
            // calculate the cost the user is paying, taking into account the users coupons
            $cost = $this->calculatePrice($data);
            $cycle = $plan->cycle;
        }

        $dojo->update([
            'subscription_id' => $subscription_id,
            'cost' => $cost,
            'cycle' => $cycle
        ]);
        // $user->notify(new DojoSubscriptionUpdated(
        //     $dojo, 
        //     $plan,
        //     $cost
        // ));
    }

    public function calculatePrice($data) {
        if (array_key_exists('discount',$data['object']) && array_key_exists('coupon',$data['object']['discount'])) {
            $discount = $data['object']['discount']['coupon']['percent_off'] * 0.01;
        } else {
            $discount = 0;
        }

        return $data['object']['plan']['amount'] * 0.01 * (1 - $discount);
    }
}
