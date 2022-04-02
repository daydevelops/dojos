<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/billing',function (Request $request) {
    $user = $request->user();
    $user->createOrGetStripeCustomer();
    return $user->redirectToBillingPortal();
});

Route::get('/documents/{name}', function(Request $request, $doc_name) {
    $text = file_get_contents(base_path() . '/' . 'documents/' . $doc_name . '.txt');
    return response($text)
        ->withHeaders([
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-store, no-cache',
            'Content-Disposition' => 'attachment; filename="' . $doc_name . '.txt"',
        ]);
});
