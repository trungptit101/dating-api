<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//import the model
use App\Models\User;
use App\Models\QuestionnaireUser;
use App\Models\Question;

//import the Validator
use Illuminate\Support\Facades\Validator;

use Auth;
use Hash;

class UserApiController extends Controller
{
    public function registerUser(Request $req)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'gender' => 'required',
            'lookingGender' => 'required',
            'age' => 'required',
            'password' => 'required|min:6',
        ];

        $customMessage = [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email is invalid',
            'email.unique' => 'Email is already taken',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
            'age.required' => 'Age is required',
            'gender.required' => 'Gender is required',
        ];

        $validation = Validator::make($req->all(), $rules, $customMessage);

        //here 422 means unprocessable entity
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'age' => $req->age,
            'gender' => $req->gender,
            'lookingGender' => $req->lookingGender,
            'password' => Hash::make($req->password),
        ]);

        if (Auth::attempt(['email' => $req->email, 'password' => $req->password])) {
            $user = User::where('email', $req->email)->first();
            $access_token = $user->createToken($req->email)->accessToken;
            User::where('email', $req->email)->update([
                'access_token' => $access_token,
            ]);

            $questionsList = Question::query()->get();
            $questionUser = array();
            foreach ($questionsList as $item) {
                $questionItem = array(
                    'answers' => json_encode([]),
                    'userId' => Auth::user()->id,
                    'questionId' => $item->id
                );
                array_push($questionUser, $questionItem);
            }
            QuestionnaireUser::insert($questionUser);
            return response()->json([
                'message' => 'User registered Successfully',
                'access_token' => $access_token,
                'user' => $user,
                'code' => 200
            ], 201);
        } else {
            return response()->json([
                'message' => 'Register account fail!',
            ], 422);
        }
    }

    public function loginUser(Request $req)
    {
        //validate the request
        $rules = [
            'email' => 'required|email|exists:users',
            'password' => 'required|min:6',
        ];

        $customMessage = [
            'email.required' => 'Email is required',
            'email.email' => 'Email is invalid',
            'email.exists' => 'Email do not exists',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
        ];

        $validation = Validator::make($req->all(), $rules, $customMessage);

        //here 422 means unprocessable entity
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors(),
            ], 422);
        }
        if (Auth::attempt(['email' => $req->email, 'password' => $req->password])) {
            $user = User::where('email', $req->email)->first();
            $access_token = $user->createToken($req->email)->accessToken;
            User::where('email', $req->email)->update([
                'access_token' => $access_token,
            ]);
            return response()->json([
                'message' => 'User login successfully',
                'access_token' => $access_token,
                'user' => $user,
                'code' => 200

            ], 200);
        } else {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 422);
        }
    }
}
