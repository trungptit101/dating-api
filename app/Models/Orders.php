<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    const PAYMENT_STATUS_INPROGRESS = 1;
    const PAYMENT_STATUS_CANCEL = 2;
    const PAYMENT_STATUS_COMPLETE = 3;

    protected $table = 'orders';
    protected $fillable = [
        'userId',
        'packageId',
        'price',
        'code',
        'payment_status',
        "months",
        "unit",
    ];
}
