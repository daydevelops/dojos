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
        return $this->belongsTo(Subscription::class);
    }

    // is the user subscribed to a stripe plan
    public function isSubscribed()
    {
        return $this->subscription_id != null && $this->subscription->stripe_status == "active";
    }

    // gt any incomplete subscriptions in our database for this dojo
    public function getIncompleteSubscriptions() {
        return Subscription::where(['name'=>'dojo-'.$this->id,'stripe_status'=>'incomplete'])->get();
    }
}
