<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caution extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'payment_intent_id',
        'montant',
        'montant_paye',
        'status',
    ];

    public function user() { 
        return $this->belongsTo(User::class); 
    }
}
