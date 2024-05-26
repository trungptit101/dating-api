<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterSurvey extends Model
{
    protected $table = 'filter_survey';
    protected $fillable = [
        'questions_id',
    ];
}
