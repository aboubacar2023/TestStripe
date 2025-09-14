<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'stripe_subscription_id', 'stripe_price_id',
        'status', 'trial_ends_at', 'ends_at',
    ];

    protected $dates = ['trial_ends_at', 'ends_at'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
