<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//import the model
use App\Models\User;
use App\Models\UserDating;
use App\Models\QuestionnaireUser;
use App\Models\Question;
use App\Notifications\SendMailForgotPasswordOTP;
use Illuminate\Support\Facades\Notification;

//import the Validator
use Illuminate\Support\Facades\Validator;

use Auth;
use Facade\FlareClient\View;
use Hash;

use function PHPUnit\Framework\isEmpty;

class UserApiController extends Controller
{
    public function listCandidate(Request $request)
    {
        $candidates = User::query()
            ->with("dating")
            ->select("users.*")
            ->join("questionnaire_user", "questionnaire_user.userId", "=", "users.id")
            ->where("role", User::Candidate);
        if ($request->input("filter")) {
            $filter = json_decode($request->input("filter"));
            foreach ($filter as $key => $condition) {
                $candidates = $candidates->where("questionnaire_user.questionId", "=", $key);
                foreach (json_decode($condition) as $item) {
                    $candidates->whereJsonContains("questionnaire_user.answers", $item);
                }
            }
        }
        $candidates = $candidates->distinct("users.id")
            ->orderBy("users.id", "asc")
            ->paginate($request->input("perPage"), ["*"], "page", $request->input("page"));
        return response()->json([
            "data" => $candidates,
            "code" => 200
        ], 200);
    }

    public function listRequestDating(Request $request)
    {
        $requestsDating = UserDating::all();
        $requestsDating = collect($requestsDating)->map(function ($request) {
            $user = User::find($request["userId"]);
            $partnerIds = explode(",", $request["partnerId"]);
            $partners = User::query()->whereIn("id", $partnerIds)->get();
            $request["user"] = $user;
            $request["partners"] = $partners;
            return $request;
        })->values();

        return response()->json([
            "data" => $requestsDating,
            "code" => 200
        ], 200);
    }

    public function updateProcessDating($id)
    {
        $requestsDating = UserDating::find($id);
        $requestsDating->isComplete = UserDating::complete;
        $requestsDating->save();
        return response()->json([
            "message" => "Complete process dating successfully!",
        ], 200);
    }

    public function deleteCandidate($id)
    {
        User::query()
            ->where("id", $id)
            ->delete();
        return response()->json([
            "message" => "Delete candidate successfully!",
            "code" => 200
        ], 200);
    }

    public function registerUser(Request $req)
    {
        $rules = [
            "name" => "required",
            "email" => "required|email|unique:users",
            "gender" => "required",
            "phone" => "required",
            "lookingGender" => "required",
            "age" => "required",
            "password" => "required|min:6",
        ];

        $customMessage = [
            "name.required" => "Name is required",
            "email.required" => "Email is required",
            "phone.required" => "Phone is required",
            "email.email" => "Email is invalid",
            "email.unique" => "Email is already exist",
            "password.required" => "Password is required",
            "password.min" => "Password must be at least 6 characters",
            "age.required" => "Age is required",
            "gender.required" => "Gender is required",
        ];

        $validation = Validator::make($req->all(), $rules, $customMessage);

        //here 422 means unprocessable entity
        if ($validation->fails()) {
            return response()->json([
                "message" => $validation->errors(),
            ], 422);
        }

        // if ($req->hasFile('image_dating')) {
        //     $url_image_dating = array();
        //     foreach ($req->file('image_dating') as $fileBusiness) {
        //         $filenameBusiness = time() . '_' . $fileBusiness->getClientOriginalName();
        //         $filePathBusiness = 'uploads/dating/';
        //         $fileBusiness->move($filePathBusiness, $filenameBusiness);
        //         array_push($url_image_dating, '/uploads/dating/' . $filenameBusiness);
        //     }
        // }

        User::create([
            "avatar" => $req->avatar,
            "name" => $req->name,
            "email" => $req->email,
            "age" => $req->age,
            "phone" => $req->phone,
            "gender" => $req->gender,
            "lookingGender" => $req->lookingGender,
            // "image_dating" => implode(",", $url_image_dating),
            "password" => Hash::make($req->password)
        ]);
        return response()->json([
            "code" => 200
        ], 201);
    }
    public function processAfterRegister(Request $req)
    {
        if (Auth::attempt(["email" => $req->email, "password" => $req->password])) {
            $user = User::where("email", $req->email)->first();
            $access_token = $user->createToken($req->email)->accessToken;
            User::where("email", $req->email)->update([
                "access_token" => $access_token,
            ]);

            $questionsList = Question::query()->get();
            $questionUser = array();
            foreach ($questionsList as $item) {
                $questionItem = array(
                    "answers" => json_encode([]),
                    "userId" => Auth::user()->id,
                    "questionId" => $item->id
                );
                array_push($questionUser, $questionItem);
            }
            QuestionnaireUser::insert($questionUser);
            return response()->json([
                "message" => "User registered Successfully",
                "access_token" => $access_token,
                "user" => $user,
                "code" => 200
            ], 201);
        } else {
            return response()->json([
                "message" => "Register account fail!",
            ], 422);
        }
    }

    public function loginUser(Request $req)
    {
        //validate the request
        $rules = [
            "email" => "required|email|exists:users",
            "password" => "required",
        ];

        $customMessage = [
            "email.required" => "Email is required",
            "email.email" => "Email is invalid",
            "email.exists" => "Email do not exists",
            "password.required" => "Password is required",
        ];

        $validation = Validator::make($req->all(), $rules, $customMessage);

        //here 422 means unprocessable entity
        if ($validation->fails()) {
            return response()->json([
                "message" => $validation->errors(),
            ], 422);
        }
        if (Auth::attempt(["email" => $req->email, "password" => $req->password])) {
            $user = User::where("email", $req->email)->first();
            $access_token = $user->createToken($req->email)->accessToken;
            User::where("email", $req->email)->update([
                "access_token" => $access_token,
            ]);
            return response()->json([
                "message" => "User login successfully",
                "access_token" => $access_token,
                "user" => $user,
                "code" => 200

            ], 200);
        } else {
            return response()->json([
                "message" => "Invalid email or password",
            ], 422);
        }
    }

    public function getUserDetail($id)
    {
        $user = User::find($id);
        return response()->json($user, 200);
    }

    public function updateUserProfile(Request $request)
    {
        Auth::user()->name = $request->input("name");
        Auth::user()->age = $request->input("age");
        Auth::user()->phone = $request->input("phone");
        Auth::user()->favorite = $request->input("favorite");
        Auth::user()->weight = $request->input("weight");
        Auth::user()->height = $request->input("height");
        Auth::user()->skin_color = $request->input("skin_color");
        Auth::user()->blood_group = $request->input("blood_group");
        Auth::user()->eye_color = $request->input("eye_color");
        Auth::user()->avatar = $request->input("avatar");
        Auth::user()->save();
        return response()->json(Auth::user(), 200);
    }

    public function forgotPassword(Request $request)
    {
        $user = User::query()->where('email', $request->email)->first();
        if (empty($user))
            return response()->json([
                "message" => "Email not exists!",
            ], 422);
        $otp = rand(1000, 9999);
        $data = [
            'otp' => $otp,
            'name' => $user->name,
        ];
        $user->otp = $otp;
        $user->save();
        Notification::route('mail', $request->email)->notify(
            new SendMailForgotPasswordOTP($data)
        );
    }

    public function resetPassword(Request $request)
    {
        $user = User::query()->where('email', $request->email)->first();
        if (empty($user))
            return response()->json([
                "message" => "User not exists!",
            ], 422);

        if ($user->otp == $request->otp && isset($user->otp)) {
            $user->otp = "";
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([
                "message" => "successfully!",
            ], 200);
        }
        return response()->json([
            "message" => "error",
        ], 422);
    }
}
