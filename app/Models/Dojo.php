<?php

namespace App\Models;

// use App\Notifications\DojoSubscriptionUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Subscription;

class Dojo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = ["user", "subscription_id"];

    protected $appends = ['is_active'];

    public static function boot()
    {
        parent::boot();
        static::deleting(function (Dojo $dojo) {
            // cancel subscription before deleting
            if ($dojo->isSubscribed()) {
                $dojo->unsubscribe();
            }
        });
    }

    public function getIsActiveAttribute()
    {
        return $this->user->is_active;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // is the user subscribed to a stripe plan
    public function isSubscribed()
    {
        return $this->subscription_id != null && $this->subscription->stripe_status == "active";
    }

    public function unsubscribe() {
        if ($this->isSubscribed()) {
            $current_subscription = $this->subscription;
        }
        if ($this->user->subscribed($current_subscription->name)) {
            // if this is the last item for that subscription
            if ($current_subscription->quantity == 1) {
                $current_subscription->cancelNow();
            } else {
                // remove one dojo from the plan
                $current_subscription->decrementQuantity();
            }
        }
        $this->update(['subscription_id' => null]);
    }

    public function subscribe($plan) {
        $user = $this->user;
        // if owner has a dojo on the new subscription already, increase quantity
        if ($user->subscribed($plan->description)) {
            $subscription = $user->subscription($plan->description);
            $subscription->incrementQuantity();
        } else {
            // else create a new subscription
            $subscription = $user
                ->newSubscription($plan->description, $plan->product_id)
                ->create(request('payment_method'),[],['metadata' => ['dojo_id' => $this->id]]);
        }
        $this->update(['subscription_id' => $subscription->id]);
    }

    // get any incomplete subscriptions in our database for this dojo
    public function getIncompleteSubscriptions() {
        return Subscription::where(['name'=>'dojo-'.$this->id,'stripe_status'=>'incomplete'])->get();
    }
}
