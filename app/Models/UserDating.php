<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDating extends Model
{
    const inProgress = 0;
    const complete = 0;

    protected $table = 'user_dating';
    protected $fillable = [
        'userId',
        'partnerId',
        'isComplete'
    ];
}
