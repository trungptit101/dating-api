<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//import the model
use App\Models\User;
use App\Models\UserDating;
use App\Models\QuestionnaireUser;
use App\Models\Question;
use App\Models\Orders;
use App\Models\Contacts;
use App\Models\PaymentPackage;

use App\Notifications\SendMailPaymentInternetBanking;
use App\Notifications\SendMailConfirmOrderSuccess;
use App\Notifications\SendMailConfirmOrderFail;
use Illuminate\Support\Facades\Notification;

//import the Validator
use Illuminate\Support\Facades\Validator;

use Auth;
use Facade\Ignition\Support\Packagist\Package;

use function PHPUnit\Framework\isEmpty;

class HomeController extends Controller
{
    public function getListContact(Request $request)
    {
        $contacts = Contacts::query()
            ->orderBy("id", "desc")
            ->paginate($request->input("perPage"), ["*"], "page", $request->input("page"));
        return response()->json([
            "data" => $contacts,
            "code" => 200
        ], 200);
    }

    public function deleteContact($id)
    {
        Contacts::query()->where('id', $id)->delete();
        return response()->json([
            "message" => "Delete contact successfully!",
            "code" => 200
        ], 200);
    }

    public function createContact(Request $request)
    {
        $rules = [
            "email" => "required",
            "contact" => "required",
        ];
        $message = [
            "email.required" => "Email is required!",
            "contact.required" => "Contact is required!",
        ];

        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors(),
            ], 422);
        }

        Contacts::create([
            "email" => $request->email,
            "contact" => $request->contact,
            "issue" => $request->issue,
            "description" => $request->description,
        ]);

        return response()->json([
            "message" => "Create contact us successfully!",
            "code" => 200
        ], 200);
    }

    public function getAnalysic(Request $request)
    {
        $ordersDataVND = Orders::select(\DB::raw('month(updated_at) as month'), \DB::raw('SUM(price) as totalMoney'))
            ->whereYear('updated_at', $request->input("year"))
            ->where('payment_status', Orders::PAYMENT_STATUS_COMPLETE)
            ->where('unit', 'VND')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->pluck('totalMoney', 'month')
            ->toArray();
        $ordersDataUSD = Orders::select(\DB::raw('month(updated_at) as month'), \DB::raw('SUM(price) as totalMoney'))
            ->whereYear('updated_at', $request->input("year"))
            ->where('payment_status', Orders::PAYMENT_STATUS_COMPLETE)
            ->where('unit', 'USD')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->pluck('totalMoney', 'month')
            ->toArray();
        $users = User::select(\DB::raw('month(created_at) as month'), \DB::raw('COUNT(*) as numberUser'))
            ->whereYear('created_at', $request->input("year"))
            ->where('role', User::Candidate)
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->pluck('numberUser', 'month')
            ->toArray();
        return response()->json([
            'users' => $this->formatStatisticData($users),
            'ordersDataVND' => $this->formatStatisticData($ordersDataVND),
            'ordersDataUSD' => $this->formatStatisticData($ordersDataUSD),
        ]);
    }

    private function formatStatisticData($data)
    {
        for ($i = 1; $i <= 12; $i++) {
            if (!array_key_exists($i, $data)) {
                $data[$i] = 0;
            }
        }
        ksort($data);

        return $data;
    }

    public function createOrder(Request $request)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $package = PaymentPackage::find($request->input("id"));
        if (empty($package))
            return response()->json([
                "message" => "Create order fail!",
            ], 400);
        $vnp_TxnRef = rand(1000, 10000000); //Mã giao dịch thanh toán tham chiếu của merchant
        $vnp_Amount = $package->price_vnpay; // Số tiền thanh toán
        $vnp_Locale = "vi"; //Ngôn ngữ chuyển hướng thanh toán
        $vnp_BankCode = $request->input("methodPaymentVnPay"); //Mã phương thức thanh toán
        $vnp_IpAddr = $request->ip(); //IP Khách hàng thanh toán
        $startTime = date("YmdHis");
        $expire = date('YmdHis', strtotime('+5 minutes', strtotime($startTime)));

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => env("vnp_TmnCode"),
            "vnp_Amount" => $vnp_Amount * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => "Thanh toan GD: " . $vnp_TxnRef,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $request->input("returnUrl") . "/payment/complete",
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
            "price" => $package->price_vnpay,
            "months" => $package->months,
            "unit" => "VNPAY",
            "code" => $vnp_TxnRef,
            "payment_status" => Orders::PAYMENT_STATUS_INPROGRESS,
        ]);
        return response()->json([
            "vnp_Url" => $vnp_Url,
            "code" => 200
        ], 200);
    }

    public function createOrderPaypal(Request $request)
    {
        $package = PaymentPackage::find($request->input("id"));
        $vnp_TxnRef = rand(1000, 10000000); //Mã giao dịch thanh toán tham chiếu của merchant
        $order = Orders::create([
            "userId" => Auth::user()->id,
            "packageId" => $package->id,
            "price" => $package->price_paypal,
            "months" => $package->months,
            "unit" => "PAYPAL",
            "code" => $vnp_TxnRef,
            "payment_status" => Orders::PAYMENT_STATUS_COMPLETE,
        ]);
        return response()->json([
            "order" => $order,
            "code" => 200
        ], 200);
    }

    public function paymentBanking(Request $request)
    {
        $package = PaymentPackage::find($request->input("id"));
        $vnp_TxnRef = rand(1000, 10000000); //Mã giao dịch thanh toán tham chiếu của merchant
        $order = Orders::create([
            "userId" => Auth::user()->id,
            "packageId" => $package->id,
            "lang" => $request->input("lang"),
            "price" => $request->input("lang") == "en" ? $package->price_paypal : $package->price_vnpay,
            "months" => $package->months,
            "unit" => "INTERNET-BANKING",
            "code" => $vnp_TxnRef,
            "content" => $request->input("content"),
            "payment_status" => Orders::PAYMENT_STATUS_INPROGRESS,
        ]);

        $data = [
            'candidate' => Auth::user()->name,
            'content' => $request->input("content"),
            'amount' => $request->input("content"),
            'unit' => $request->input("lang") == "en" ? "USD" : "VNĐ",
        ];
        Notification::route('mail', 'jenbusiness.sg@gmail.com')->notify(
            new SendMailPaymentInternetBanking($data)
        );
        return response()->json([
            "order" => $order,
            "code" => 200
        ], 200);
    }

    public function getRequestBanking(Request $request)
    {
        $orders = Orders::query()
            ->with("user")
            ->where("unit", "INTERNET-BANKING")
            ->orderBy("id", "desc")
            ->paginate($request->input("perPage"), ["*"], "page", $request->input("page"));
        return response()->json([
            "data" => $orders,
            "code" => 200
        ], 200);
    }

    public function finishRequestBanking(Request $request)
    {
        $order = Orders::find($request->id);
        $order->payment_status = $request->status;
        $order->save();

        $user = User::query()->where('id', $order->userId)->first();
        $data = [
            'name' => $user->name,
        ];
        if ($request->status == 2) {
            Notification::route('mail', $user->email)->notify(
                new SendMailConfirmOrderFail($data)
            );
        } else {
            Notification::route('mail', $user->email)->notify(
                new SendMailConfirmOrderSuccess($data)
            );
        }
        return response()->json([
            "code" => 200
        ], 200);
    }

    public function cancelOrderPaypal(Request $request)
    {
        $package = PaymentPackage::find($request->input("id"));
        $vnp_TxnRef = rand(1000, 10000000); //Mã giao dịch thanh toán tham chiếu của merchant
        $order = Orders::create([
            "userId" => Auth::user()->id,
            "packageId" => $package->id,
            "price" => $package->price_paypal,
            "months" => $package->months,
            "unit" => "PAYPAL",
            "code" => $vnp_TxnRef,
            "payment_status" => Orders::PAYMENT_STATUS_CANCEL,
        ]);
    }


    public function getOrderDetail(Request $request)
    {
        if ($request->input("vnp_TxnRef")) {
            $order = Orders::query()
                ->where("userId", Auth::user()->id)
                ->where("payment_status", Orders::PAYMENT_STATUS_COMPLETE)
                ->where("code", $request->input("vnp_TxnRef"))
                ->orderBy("created_at", "desc")
                ->first();
        } else {
            $order = Orders::query()
                ->where("userId", Auth::user()->id)
                ->where("payment_status", Orders::PAYMENT_STATUS_COMPLETE)
                ->orderBy("created_at", "desc")
                ->first();
        }
        if (empty($order))
            return response()->json([
                "message" => "Payment failed!",
                "order" => $order
            ], 400);
        if ($order->payment_status != Orders::PAYMENT_STATUS_INPROGRESS) {
            $statusMsg = "";
            if ($order->payment_status == Orders::PAYMENT_STATUS_CANCEL) {
                $statusMsg = "cancelled";
            } else $statusMsg = "completed";
            return response()->json([
                "message" => "This order has been " . $statusMsg . "!",
                "status" => $statusMsg,
                "order" => $order,
            ], 200);
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

    public function getOrderBankingDetail(Request $request)
    {
        $order = Orders::query()
            ->where("userId", Auth::user()->id)
            ->where("payment_status", Orders::PAYMENT_STATUS_INPROGRESS)
            ->where("unit", "INTERNET-BANKING")
            ->orderBy("created_at", "desc")
            ->first();
        return response()->json([
            "message" => "Payment failed!",
            "order" => $order
        ], 200);
    }

    public function getPartnerSuggestion()
    {
        $partnersSuggestion = User::query()
            ->where("role", User::Candidate)
            ->where("id", "!=", Auth::user()->id)
            ->where("gender", Auth::user()->lookingGender)
            ->get();
        return response()->json([
            "data" => $partnersSuggestion
        ], 200);
    }

    public function processDating(Request $request)
    {
        $partnersId = $request->input("partnersId");
        UserDating::create([
            "userId" => Auth::user()->id,
            "partnerId" => implode(',', $partnersId),
            "isComplete" => false,
        ]);
        return response()->json([
            "message" => "Dating process is being done!"
        ], 200);
    }

    public function getProcessDetail(Request $request)
    {
        $process = UserDating::query()->where("userId", Auth::user()->id)->first();
        return response()->json([
            "process" => $process
        ], 200);
    }
}
