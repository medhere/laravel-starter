<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function(){
    return view('home.index');
});

Route::controller(AuthController::class)->group(function () {
    Route::match(['get', 'post'], '/signin', 'signin')->name('signin');
    Route::match(['get', 'post'], '/signup', 'signup')->name('signup');
    Route::get('/signout', 'signout')->middleware('auth')->name('signout');
    Route::match(['get', 'post'], '/forgotpassword', 'forgotpassword')->name('forgotpassword');
    Route::match(['get', 'post'], '/resetforgotpassword', 'resetforgotpassword')->name('resetforgotpassword');
});


Route::controller()
    ->prefix('admin')->name('admin.')
    ->middleware(['auth', 'role:all'])
    ->group(function () {
    
});
