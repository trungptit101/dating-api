<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionnaireUser extends Model
{
    protected $table = 'questionnaire_user';

    protected $fillable = [
        'answers', 'userId', 'questionId'
    ];
}
