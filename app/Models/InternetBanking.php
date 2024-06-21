<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternetBanking extends Model
{
    protected $table = 'internet_banking';

    protected $fillable = [
        'country', 'qrcode', 'account_name', 'account_number', 'bank'
    ];
}
