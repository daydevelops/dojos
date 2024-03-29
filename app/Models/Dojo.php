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

    protected $hidden = ["user", "subscription_id", "cost", "cycle"];

    protected $appends = ['is_active', 'subscription_level'];

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

    // returns either "free", "standard", or "premium" depending on what type of subscription this dojo has
    public function getSubscriptionLevelAttribute() {
        $standards = [config('payments.plans.standard.monthly'),config('payments.plans.standard.monthly')];
        $premiums = [config('payments.plans.premium.monthly'),config('payments.plans.premium.monthly')];

        if (!$this->isSubscribed()) {
            return "free";
        } else {
            $sub = $this->subscription;
            if (in_array($sub->stripe_plan,$standards)) {
                return "standard";
            } else if (in_array($sub->stripe_plan,$premiums)) {
                return "premium";
            }
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
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
        $this->update([
            'subscription_id' => null,
            'cost' => null,
            'cycle' => null
        ]);
    }

    public function subscribe($plan) {
        $user = $this->user;
        $coupon = $user->highestCoupon();
        
        // get coupon to apply
        if ($coupon) {
            $subscription_name = $plan->description . ": " . $coupon->description;
        } else {
            $subscription_name = $plan->description;
        }

        // if owner has a dojo on this subscription already which also uses the same coupon, increase quantity
        if ($user->subscribed($subscription_name)) {
            $subscription = $user->subscription($subscription_name);
            $subscription->incrementQuantity();
            
        } else {
            // else create a new subscription
            if ($coupon) {
                $subscription = $user
                    ->newSubscription($subscription_name, $plan->product_id)
                    ->withCoupon($coupon->code)
                    ->create(request('payment_method'),[],['metadata' => ['dojo_id' => $this->id]]);
            } else {
                $subscription = $user
                    ->newSubscription($subscription_name, $plan->product_id)
                    ->create(request('payment_method'),[],['metadata' => ['dojo_id' => $this->id]]);
            }
        }
        $cost = $user->getCostFor($plan);
        $this->update([
            'subscription_id' => $subscription->id,
            'cost' => $cost,
            'cycle' => $plan->cycle
        ]);
    }

    // get any incomplete subscriptions in our database for this dojo
    public function getIncompleteSubscriptions() {
        return Subscription::where(['name'=>'dojo-'.$this->id,'stripe_status'=>'incomplete'])->get();
    }
}
