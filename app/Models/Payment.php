<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'transaction_id' ,
        'amount',
        'payer_name',
        'payer_email',
        'payment_type',
        'description'
    ];
}
