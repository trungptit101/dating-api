<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//import the model
use App\Models\User;
use App\Models\PaymentPackage;

//import the Validator
use Illuminate\Support\Facades\Validator;

use Auth;

class PaymentPackageController extends Controller
{

    public function listPackage()
    {
        $paymentPackage = PaymentPackage::query()->get();
        return response()->json([
            "data" => $paymentPackage,
            "code" => 200
        ], 200);
    }

    public function createPackage(Request $request)
    {
        $rules = [
            "months" => "required",
            "gender" => "required",
            "price_paypal" => "required",
            "price_vnpay" => "required",
        ];
        $message = [
            "months.required" => "Months is required!",
            "gender.required" => "Gender is required!",
            "price_paypal.required" => "Price USD is required!",
            "price_vnpay.required" => "Price VNĐ is required!",
        ];

        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors(),
            ], 422);
        }
        PaymentPackage::create([
            "months" => $request->input("months"),
            "gender" => $request->input("gender"),
            "price_paypal" => $request->input("price_paypal"),
            "price_vnpay" => $request->input("price_vnpay"),
        ]);

        return response()->json([
            "message" => "Create package successfully!",
            "code" => 200
        ], 200);
    }

    public function updatePackage(Request $request, $id)
    {
        $package = PaymentPackage::find($id);
        $package->gender = $request->gender;
        $package->months = $request->months;
        $package->price_paypal = $request->price_paypal;
        $package->price_vnpay = $request->price_vnpay;
        $package->save();

        return response()->json([
            "message" => "Update package successfully!",
            "code" => 200
        ], 200);
    }

    public function deletePackage($id)
    {
        PaymentPackage::query()
            ->where("id", $id)
            ->delete();

        return response()->json([
            "message" => "Delete payment package successfully!",
            "code" => 200
        ], 200);
    }
}
