<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caution extends Model
{
    use HasFactory;
    protected $fillable = [
        'stripe_payment_intent_id',
        'user_id',
        'amount',
        'status',
        'trial_ends_at'
    ];
}
