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
        $paymentPackage = PaymentPackage::all();
        return response()->json([
            "data" => $paymentPackage,
            "code" => 200
        ], 200);
    }

    public function createPackage(Request $request)
    {
        $rules = [
            "months" => "required",
            "price" => "required",
            "unit" => "required",
        ];
        $message = [
            "months.required" => "Months is required!",
            "price.required" => "Price is required!",
            "unit.required" => "Unit is required!",
        ];

        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors(),
            ], 422);
        }
        PaymentPackage::create([
            "months" => $request->input("months"),
            "price" => $request->input("price"),
            "unit" => $request->input("unit"),
        ]);

        return response()->json([
            "message" => "Create package successfully!",
            "code" => 200
        ], 200);
    }

    public function updatePackage(Request $request, $id)
    {
        $rules = [
            "months" => "required",
            "price" => "required",
            "unit" => "required",
        ];
        $message = [
            "months.required" => "Months is required!",
            "price.required" => "Price is required!",
            "unit.required" => "Unit is required!",
        ];

        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors(),
            ], 422);
        }
        $package = PaymentPackage::find($id);
        $package->months = $request->months;
        $package->price = $request->price;
        $package->unit = $request->unit;
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
