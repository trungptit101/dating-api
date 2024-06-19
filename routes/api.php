<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//import the Controller
use App\Http\Controllers\UserApiController;
use App\Http\Controllers\PaymentPackageController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DiscountStrategyController;

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
Route::post('/process-after-register', UserApiController::class . '@processAfterRegister');
Route::post('/forgot-password', UserApiController::class . '@forgotPassword');
Route::post('/reset-password', UserApiController::class . '@resetPassword');
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
        Route::post('/update-order', QuestionController::class . '@updateOrder');
        Route::put('/update-questionaire-user/{id}', QuestionController::class . '@updateQuestionaireUser');
        Route::delete('/delete/{id}', QuestionController::class . '@deleteQuestion');
        Route::get('/settings-filter', QuestionController::class . '@getSettingsFilter');
        Route::get('/questions/settings', QuestionController::class . '@getQuestionSettingsFilter');
        Route::post('/up-sert-settings-filter', QuestionController::class . '@upSertSettingsFilter');
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
        Route::post('/profile', UserApiController::class . '@updateUserProfile');
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
        Route::get('/list', UserApiController::class . '@listCandidate')->middleware("CheckAdmin");
        Route::delete('/delete/{id}', UserApiController::class . '@deleteCandidate');
        Route::get('/dating-requests/list', UserApiController::class . '@listRequestDating')->middleware("CheckAdmin");
        Route::post('/dating-process/update/{id}', UserApiController::class . '@updateProcessDating')->middleware("CheckAdmin");
    }
);

// payment package
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

// order
Route::group(
    [
        'prefix' => 'order',
        'as' => 'order.',
        'middleware' => ['auth:api'],
    ],
    function () {
        Route::post('/create', HomeController::class . '@createOrder');
        Route::post('/create-paypal', HomeController::class . '@createOrderPaypal');
        Route::post('/cancel-paypal', HomeController::class . '@cancelOrderPaypal');
        Route::get('/detail', HomeController::class . '@getOrderDetail');
    }
);

// partner
Route::group(
    [
        'prefix' => 'partner',
        'as' => 'partner.',
        'middleware' => ['auth:api'],
    ],
    function () {
        Route::get('/suggestion', HomeController::class . '@getPartnerSuggestion');
        Route::post('/process/dating', HomeController::class . '@processDating');
        Route::get('/process/detail', HomeController::class . '@getProcessDetail');
    }
);

// analysic
Route::group(
    [
        'prefix' => 'analysic',
        'as' => 'analysic.',
        'middleware' => ['auth:api', 'CheckAdmin'],
    ],
    function () {
        Route::get('/index', HomeController::class . '@getAnalysic');
    }
);

// partner
Route::group(
    [
        'prefix' => 'contact',
        'as' => 'contact.',
    ],
    function () {
        Route::get('/list', HomeController::class . '@getListContact')->middleware('auth:api', 'CheckAdmin');
        Route::post('/create', HomeController::class . '@createContact');
        Route::delete('/delete/{id}', HomeController::class . '@deleteContact')->middleware('auth:api', 'CheckAdmin');
    }
);

// strategy
Route::group(
    [
        'prefix' => 'strategy',
        'as' => 'strategy.',
    ],
    function () {
        Route::get('/list', DiscountStrategyController::class . '@listStrategies')->middleware('auth:api');
        Route::post('/create', DiscountStrategyController::class . '@createStrategy')->middleware('auth:api', 'CheckAdmin');
        Route::put('/update/{id}', DiscountStrategyController::class . '@updateStrategy')->middleware('auth:api', 'CheckAdmin');
        Route::delete('/delete/{id}', DiscountStrategyController::class . '@deleteStrategy')->middleware('auth:api', 'CheckAdmin');
    }
);
