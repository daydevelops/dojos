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
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        // Retrieve the request's body and parse it as JSON
        $input = @file_get_contents("php://input");
        $event_json = json_decode($input);
        $event = \Stripe\Event::retrieve($event_json->id);

        // only listening for subscription events
        if (substr($event->type,0,21) != 'customer.subscription') {
            return;
        }

        $dojo_id = $event->data->object->metadata['dojo_id'];
        $plan_id = $event->data->object->plan['id'];
        $dojo = Dojo::find($dojo_id);
        if ($event->type == 'customer.subscription.deleted') {
            $subscription_id = null;
        } else {
            $subscription_id = DB::table('subscriptions')->where(['stripe_id'=>$event->data->object->id])->get()[0]->id;
        }
        $dojo->update(['subscription_id' => $subscription_id]);
        $user = $dojo->user;
        $user->notify(new DojoSubscriptionUpdated(
            $dojo, 
            StripeProduct::where(['product_id' => $plan_id])->first()
        ));
    }
}
