<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchatProduit extends Model
{
    use HasFactory;
    protected $fillable = [
        'stripe_payment_intent_id',
        'stripe_customer_id',
        'user_id',
        'produit_id',
        'quantite'
    ];
}
