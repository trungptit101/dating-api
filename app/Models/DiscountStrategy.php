<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountStrategy extends Model
{
    protected $table = 'campain_marketing';

    protected $fillable = [
        'gender', 'start', 'discount', 'end'
    ];
}
