<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dojo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = ["user"];

    protected $appends = ['is_active'];

    public function getIsActiveAttribute() {
        return $this->user->is_active;
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function subscription() {
        return $this->belongsTo(\Laravel\Cashier\Subscription::class);  
    }

}
