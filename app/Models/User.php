<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_complete_survey',
        'gender',
        'lookingGender',
        'age',
        'role',
        'avatar',
        'favorite',
        'weight',
        'height',
        'skin_color',
        'blood_group',
        'phone',
        'eye_color',
        // 'image_dating'
    ];

    const Admin = 1;
    const Candidate = 0;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'access_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function dating()
    {
        return $this->hasOne('App\Models\UserDating', 'userId', 'id');
    }
}
