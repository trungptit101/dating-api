<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//import the model
use App\Models\InternetBanking;

//import the Validator
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Auth;

class InternetBankingController extends Controller
{
    public function list(Request $request)
    {
        $data = InternetBanking::query()
            ->orderBy("id", "asc")->get();
        return response()->json([
            "data" => $data,
            "code" => 200
        ], 200);
    }

    public function create(Request $request)
    {
        InternetBanking::create([
            "country" => $request->input("country"),
            "qrcode" => $request->input("qrcode"),
            "account_name" => $request->input("account_name"),
            "account_number" => $request->input("account_number"),
            "bank" => $request->input("bank"),
        ]);

        return response()->json([
            "message" => "successfully!",
            "code" => 200
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $item = InternetBanking::find($id);
        $item->country = $request->country;
        $item->qrcode = $request->qrcode;
        $item->account_name = $request->input("account_name");
        $item->account_number = $request->input("account_number");
        $item->bank = $request->input("bank");
        $item->save();

        return response()->json([
            "message" => "successfully!",
            "code" => 200
        ], 200);
    }

    public function delete($id)
    {
        InternetBanking::query()
            ->where("id", $id)
            ->delete();

        return response()->json([
            "message" => "successfully!",
            "code" => 200
        ], 200);
    }
}
