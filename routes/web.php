<?php

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

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Feature 1 : Paiement avec prix dynamique, récuperation du prix depuis la DB, utilisation du webhook pour des
// verifications de payement effectif
Route::post('/feature-1', [PaymentController::class, 'index']);
Route::post('/stripe/webhook', [PaymentController::class, 'webhook']);
Route::view('/success', 'success')->name('checkout.success');
Route::view('/cancel', 'cancel')->name('checkout.cancel');


// Feacture 2 : les cautions

Route::get('/essaie/inscription', [CautionController::class, 'inscription'])->name('essaie.inscription');

// Démarrer l'essai (création de la session Stripe)
Route::get('/essaie/start', [CautionController::class, 'startEssaie'])->name('essaie.start');

// Page de suivi de l'essai (success/cancel redirige ici)
Route::get('/essaie', [CautionController::class, 'showEssaie'])->name('essaie');

// Actions utilisateur : confirmer ou annuler l'essai
Route::post('/essaie/{caution}/confirm', [CautionController::class, 'confirmerEssaie'])->name('essaie.confirm');
Route::post('/essaie/{caution}/cancel', [CautionController::class, 'cancelEssaie'])->name('essaie.cancel');

// Webhook (NO auth) - Stripe enverra ici les événements
Route::post('/stripe/webhook', [CautionController::class, 'webhook'])->name('stripe.webhook');

// Back-office admin : liste & actions (protéger avec middleware admin)
Route::group(['prefix'=>'admin'], function () {
    Route::get('/cautions', [CautionController::class, 'adminIndex'])->name('admin.cautions.index');
    Route::post('/caution/{caution}/capture', [CautionController::class, 'adminCapture'])->name('admin.cautions.capture');
    Route::post('/caution/{caution}/cancel', [CautionController::class, 'adminCancel'])->name('admin.cautions.cancel');
});
