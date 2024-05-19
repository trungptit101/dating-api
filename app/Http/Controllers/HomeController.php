<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//import the model
use App\Models\User;
use App\Models\QuestionnaireUser;
use App\Models\Question;
use App\Models\Orders;
use App\Models\PaymentPackage;

//import the Validator
use Illuminate\Support\Facades\Validator;

use Auth;
use Facade\Ignition\Support\Packagist\Package;

class HomeController extends Controller
{
    public function createOrder(Request $request)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $package = PaymentPackage::find($request->input("id"));
        if (empty($package))
            return response()->json([
                "message" => "Create order fail!",
            ], 400);
        $vnp_TxnRef = rand(1000, 10000000); //Mã giao dịch thanh toán tham chiếu của merchant
        $vnp_Amount = $package->price; // Số tiền thanh toán
        $vnp_Locale = "en"; //Ngôn ngữ chuyển hướng thanh toán
        $vnp_BankCode = "VNBANK"; //Mã phương thức thanh toán
        $vnp_IpAddr = $request->ip(); //IP Khách hàng thanh toán
        $startTime = date("YmdHis");
        $expire = date('YmdHis', strtotime('+5 minutes', strtotime($startTime)));

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => env("vnp_TmnCode"),
            "vnp_Amount" => $vnp_Amount * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => $package->unit,
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => "Thanh toan GD: " . $vnp_TxnRef,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $request->input("returnUrl") . "/#/payment/complete",
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $expire
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData["vnp_BankCode"] = $vnp_BankCode;
        }
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = env("vnp_Url") . "?" . $query;
        $vnp_HashSecret = env("vnp_HashSecret");
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        Orders::create([
            "userId" => Auth::user()->id,
            "packageId" => $package->id,
            "price" => $package->price,
            "months" => $package->months,
            "unit" => $package->unit,
            "code" => $vnp_TxnRef,
            "payment_status" => Orders::PAYMENT_STATUS_INPROGRESS,
        ]);
        return response()->json([
            "vnp_Url" => $vnp_Url,
            "code" => 200
        ], 200);
    }


    public function getOrderDetail(Request $request)
    {
        if ($request->input("vnp_TxnRef")) {
            $order = Orders::query()->where("userId", Auth::user()->id)->where("code", $request->input("vnp_TxnRef"))->first();
        } else {
            $order = Orders::query()->where("userId", Auth::user()->id)->first();
        }
        if (empty($order))
            return response()->json([
                "message" => "fail!",
            ], 400);
        if ($order->payment_status != Orders::PAYMENT_STATUS_INPROGRESS) {
            $statusMsg = "";
            if ($order->payment_status == Orders::PAYMENT_STATUS_CANCEL) {
                $statusMsg = "cancelled";
            } else $statusMsg = "completed";
            return response()->json([
                "message" => "This order has been " . $statusMsg . "!",
                "order" => $order,
            ], 400);
        }
        $status = $order->payment_status;
        if ($request->input('vnp_ResponseCode') == '00' && $request->input('vnp_TransactionStatus') == '00') {
            $status = 3; // Trạng thái thanh toán thành công
        } else {
            $status = 2; // Trạng thái thanh toán thất bại / lỗi
        }
        $order->payment_status = $status;
        $order->save();
        return response()->json([
            "message" => "",
            "order" => $order
        ], 200);
    }

    public function getPartnerSuggestion(Request $request)
    {
        $partnersSuggestion = User::query()->where("gender", Auth::user()->lookingGender)->get();
        return response()->json([
            "data" => $partnersSuggestion
        ], 200);
    }
}
