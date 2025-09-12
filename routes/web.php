<?php

use App\Http\Controllers\PaymentController;
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

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Feature 1 : Paiement avec prix dynamique, récuperation du prix depuis la DB, utilisation du webhook pour des
// verifications de payement effectif
Route::post('/feature-1', [PaymentController::class, 'index']);
Route::post('/stripe/webhook', [PaymentController::class, 'webhook']);
Route::view('/success', 'success')->name('checkout.success');
Route::view('/cancel', 'cancel')->name('checkout.cancel');
