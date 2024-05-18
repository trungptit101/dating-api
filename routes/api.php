<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//import the Controller
use App\Http\Controllers\UserApiController;
use App\Http\Controllers\PaymentPackageController;
use App\Http\Controllers\QuestionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/*
    Laravel Passport Installation Steps

    1. composer require laravel/passport
    2. php artisan migrate
    3. php artisan passport:install
    4. In User model,
       use Laravel\Passport\HasApiTokens;
       use HasApiTokens,HasFactory,Notifiable
    5. Update AuthServiceProvider
       use Laravel\Passport\Passport;
    6. In boot method,
       Passport::routes();
    7.Update config/auth.php
      'api'=> [
           'driver' => 'passport',
           'provider' => 'users',
      ],

*/

//Laravel Passport
Route::post('/register', UserApiController::class . '@registerUser');
Route::post('/login', UserApiController::class . '@loginUser');

// question

Route::group(
    [
        'prefix' => 'question',
        'as' => 'question.',
        'middleware' => ['auth:api'],
    ],
    function () {
        Route::get('/list', QuestionController::class . '@listQuestion');
        Route::get('/questionnaire', QuestionController::class . '@listQuestionnaire');
        Route::post('/create', QuestionController::class . '@createQuestion');
        Route::post('/finish-survey', QuestionController::class . '@finishSurveyQuestion');
        Route::put('/update/{id}', QuestionController::class . '@updateQuestion');
        Route::put('/update-questionaire-user/{id}', QuestionController::class . '@updateQuestionaireUser');
        Route::delete('/delete/{id}', QuestionController::class . '@deleteQuestion');
    }
);

// user
Route::group(
    [
        'prefix' => 'user',
        'as' => 'user.',
        'middleware' => ['auth:api'],
    ],
    function () {
        Route::get('/detail/{id}', UserApiController::class . '@getUserDetail');
    }
);

// candidate
Route::group(
    [
        'prefix' => 'candidate',
        'as' => 'candidate.',
        'middleware' => ['auth:api'],
    ],
    function () {
        Route::get('/list', UserApiController::class . '@listCandidate');
    }
);

// candidate
Route::group(
    [
        'prefix' => 'payment-package',
        'as' => 'payment-package.',
        'middleware' => ['auth:api'],
    ],
    function () {
        Route::get('/list', PaymentPackageController::class . '@listPackage');
        Route::post('/create', PaymentPackageController::class . '@createPackage');
        Route::put('/update/{id}', PaymentPackageController::class . '@updatePackage');
        Route::delete('/delete/{id}', PaymentPackageController::class . '@deletePackage');
    }
);
