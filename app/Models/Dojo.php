<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dojo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['is_active'];

    public function getIsActiveAttribute() {
        return $this->user->is_active;
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
