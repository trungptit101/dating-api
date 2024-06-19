<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//import the model
use App\Models\DiscountStrategy;

//import the Validator
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Auth;

class DiscountStrategyController extends Controller
{
    public function listStrategies(Request $request)
    {
        $strategies = DiscountStrategy::query()
            ->orderBy("id", "asc")->get();
        return response()->json([
            "data" => $strategies,
            "code" => 200
        ], 200);
    }

    public function createStrategy(Request $request)
    {
        DiscountStrategy::create([
            "gender" => $request->input("gender"),
            "start" => $request->input("start"),
            "end" => $request->input("end"),
            "discount" => $request->input("discount"),
        ]);

        return response()->json([
            "message" => "successfully!",
            "code" => 200
        ], 200);
    }

    public function updateStrategy(Request $request, $id)
    {
        $strategy = DiscountStrategy::find($id);
        $strategy->gender = $request->gender;
        $strategy->start = $request->start;
        $strategy->end = $request->end;
        $strategy->discount = $request->discount;
        $strategy->save();

        return response()->json([
            "message" => "successfully!",
            "code" => 200
        ], 200);
    }

    public function deleteStrategy($id)
    {
        DiscountStrategy::query()
            ->where("id", $id)
            ->delete();

        return response()->json([
            "message" => "successfully!",
            "code" => 200
        ], 200);
    }
}
