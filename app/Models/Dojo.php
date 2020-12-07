<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dojo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = ["user", "subscription_id"];

    protected $appends = ['is_active'];
    
    public static function boot() {
	    parent::boot();
	    static::deleting(function(Dojo $dojo) {
            // cancel subscription before deleting
            if ($dojo->isSubscribed()) {
                $dojo->subscription->cancelNow();
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
        return $this->belongsTo(\Laravel\Cashier\Subscription::class);
    }

    // is the user subscribed to a stripe plan
    public function isSubscribed() {   
        return $this->subscription_id != null && $this->subscription->stripe_status=="active";
    }

    public function swapPlans($new_plan)
    {
        if ($new_plan->stripe_id == "free_plan") {
            // user is switching to free plan, cancel their subscription
            $this->cancelPlan();
        } else {
            // switching to new paid plan
            $this->subscription->swap($new_plan->stripe_id);
        }
    }

    public function cancelPlan()
    {
        $this->subscription->cancelNow();
        $this->update(['subscription_id' => null]);
    }

    public function newPlan($plan)
    {
        $subscription = auth()->user()
            ->newSubscription("dojo-" . $this->id, $plan->stripe_id)
            ->create(request('payment_method'));
        $this->update(['subscription_id' => $subscription->id]);
    }
}
