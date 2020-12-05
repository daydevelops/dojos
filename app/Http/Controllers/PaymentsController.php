<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    
    public function getIntents() {
        return auth()->user()->createSetupIntent();
    }

}
