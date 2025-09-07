<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    use HasFactory;
    protected $fillable = [
        'stripe_payment_intent_id',
        'stripe_customer_id',
        'status',
        'plan',
        'start_date',
        'end_date',
        'user_id'
    ];
}
