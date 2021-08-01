<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'coupon_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public static function boot() {
	    parent::boot();
	    static::deleting(function(User $user) {
	        foreach($user->dojos as $dojo) {
                $dojo->delete();
            }
	    });
	}

    public function dojos() {
        return $this->hasMany(Dojo::class);
    }

    public function coupon() {
        return $this->belongsTo(Coupon::class);
    }

    // given a stripe plan, and any coupon the user has, return the cost of the plan
    public function getCostFor(StripeProduct $plan) {
        $coupon = $this->highestCoupon();
        return $coupon ? $plan->price * ( 1 - 0.01 * $coupon->discount) : $plan->price;
    }

    public function highestCoupon() {
        // given that a user may have a personal coupon, and there may be global coupons to apply, return the best coupon
        $personal = $this->coupon;
        $global = globalCoupon();

        if ($personal && !$global) {
            // use has only personal coupon
            return $personal;

        } else if (!$personal && $global) {
            // user has only global coupon
            return $global;

        } else if ($personal && $global) {
            // user has both, get the highest
            return $personal->discount > $global->discount ? $personal : $global;

        } else {
            // no coupon
            return null;
        }

    }
}
