<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPackage extends Model
{
    protected $table = 'payment_package';

    protected $fillable = [
        'months', 'price', 'unit'
    ];
}
