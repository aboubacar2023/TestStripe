<?php

use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\CautionController;
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


// Feature 1 : Paiement avec prix dynamique, récuperation du prix depuis la DB, utilisation du webhook pour des
// verifications de payement effectif
// Route::get('/', function () {
//     return view('welcome');
// })->name('welcome');
// Route::post('/feature-1', [PaymentController::class, 'index']);
// Route::view('/success', 'success')->name('checkout.success');
// Route::view('/cancel', 'cancel')->name('checkout.cancel');


// Feacture 2 : les cautions

Route::get('/location', [CautionController::class, 'location'])->name('location');
Route::get('/validation', [CautionController::class, 'startLocation'])->name('validation');
Route::get('/cautions', [CautionController::class, 'index'])->name('cautions.index');
Route::post('/cautions/{caution}/capture', [CautionController::class, 'capture'])->name('cautions.capture');
Route::post('/cautions/{caution}/annule', [CautionController::class, 'annule'])->name('cautions.annule');
Route::view('/success', 'success')->name('success');
Route::view('/cancel', 'location')->name('cancel');

// Feature 3 : les abonnements

// Route::get('/activation-subscribe', [AbonnementController::class, 'index'])->name('activation-subscribe');
// Route::get('/subscribe', [AbonnementController::class, 'createCheckout'])->name('subscribe');
// Route::get('/success', [AbonnementController::class, 'success'])->name('checkout.success');
// Route::get('/cancel', [AbonnementController::class, 'cancel'])->name('checkout.cancel');

// Tous les Stripe Webhook
// NB : Utiliser un seul à la fois et mettre en commentaire les autres
// Route::post('/stripe/webhook', [PaymentController::class, 'webhook']);
Route::post('/stripe/webhook', [CautionController::class, 'webhook']);
// Route::post('/stripe/webhook', [AbonnementController::class, 'webhook']);
