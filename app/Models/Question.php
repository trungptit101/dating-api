<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Question extends Model
{
    protected $table = 'survey_question';

    protected $fillable = [
        'question', 'slug', 'options', 'type', 'description', 'background', 'question_en', 'description_en'
    ];

    public function answers()
    {
        return $this->hasOne('App\Models\QuestionnaireUser', 'questionId', 'id')
            ->where('userId', Auth::user()->id);
    }
}
