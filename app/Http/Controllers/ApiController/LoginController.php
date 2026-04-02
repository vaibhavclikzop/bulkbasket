<?php

namespace App\Http\Controllers\ApiController;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;

class LoginController extends Controller
{

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Enter valid 10-digit mobile number',
            ], 422);
        }
        try {
            $otp = rand(100000, 999999);
            $number = '91' . $request->number;
            Cache::put('otp_' . $request->number, $otp, now()->addMinutes(2));
            Session::put('otp_' . $request->number, $otp);
            Session::put('otp_expiry_' . $request->number, now()->addMinutes(2));
            $smsConfig = config('services.smswala');
            $msg = "Your OTP for login is {#var1#}. Valid for {#var2#} minutes. Do not share it with anyone - Bulk Basket India";
            $finalMsg = str_replace(
                ['{#var1#}', '{#var2#}'],
                [$otp, 2],
                $msg
            );
            $url = "{$smsConfig['url']}?"
                . "key={$smsConfig['key']}"
                . "&campaign={$smsConfig['campaign']}"
                . "&routeid={$smsConfig['routeid']}"
                . "&type=text"
                . "&contacts={$number}"
                . "&senderid={$smsConfig['sender']}"
                . "&msg=" . urlencode($finalMsg)
                . "&template_id={$smsConfig['templates']['otp']}"
                . "&pe_id={$smsConfig['pe_id']}";
            $response = Http::get($url);
            $respBody = $response->body();
            if (
                $response->successful() &&
                (stripos($respBody, 'SMS-SHOOT-ID') !== false || stripos($respBody, 'SUCCESS') !== false)
            ) {
                Log::info("✅ OTP SMS sent to {$number}, OTP={$otp}, resp={$respBody}");
                return response()->json([
                    'status' => true,
                    'message' => 'OTP sent successfully',
                ]);
            }
            Log::error("❌ OTP SMS failed for {$number}, resp={$respBody}");
            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP, try again later',
            ]);
        } catch (\Exception $e) {
            Log::error("🔥 OTP send error: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid input',
            ], 422);
        }
        $storedOtp = Cache::get('otp_' . $request->number);
        $sessionKey = 'otp_' . $request->number;
        $expiryKey = 'otp_expiry_' . $request->number;
        // $storedOtp = Session::get($sessionKey);
        $expiry = Session::get($expiryKey);
        Log::info("🔍 OTP Check for {$request->number}: entered={$request->otp}, stored={$storedOtp}, expiry={$expiry}");
        if (!$storedOtp) {
            return response()->json([
                'status' => false,
                'message' => 'OTP not found or session expired. Please request again.',
            ]);
        }
        if ($storedOtp != $request->otp) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect OTP. Please try again.',
            ]);
        }
        Session::forget($sessionKey);
        Session::forget($expiryKey);

        $user = DB::table("customer_users as a")
            ->select("a.*", "b.active", "b.supplier_id")
            ->join("customers as b", "a.customer_id", "b.id")
            ->where("a.number", $request->number)
            ->first();
        
        


        if (!$user) {
            $customerId = DB::table('customers')->insertGetId([
                'name' => 'Guest User',
                'active' => 0,
                'number' => $request->number,
                'supplier_id' => 0,
            ]);

            DB::table('customer_users')->insertGetId([
                'customer_id' => $customerId,
                'number' => $request->number,
                'name' => 'Guest User',
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Your mobile number has been verified.',
                'redirect' => 'signup step follow'
            ]);
        }
        

        if ($user->active == 0 && $user->supplier_id == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Please sign up to proceed.',
                'redirect' => 'signup'
            ]);
        }

        if ($user->active == 2) {
            return response()->json([
                'status' => false,
                'message' => 'Your account is under process.',
                'redirect' => 'pending for approve'
            ]);
        }

        $token = bin2hex(random_bytes(16));
        $agent = new \Jenssegers\Agent\Agent();
        $browser = $agent->browser();
        $version = $agent->version($browser);
        $platform = $agent->platform();

        DB::table('customer_users')->where("id", $user->id)->update([
            'web_token' => $token,
            'last_ip' => $request->ip(),
            'last_login' => now(),
            'platform' => "$browser / $version / $platform"
        ]);
        DB::table('remember_token')->insert([
            'web_token' => $token,
            'customer_id' =>   $user->customer_id,
            'user_id' =>   $user->id,
            'last_ip' => $request->ip(),
            'last_login' => now(),
            'platform' => "$browser / $version / $platform"
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
            'redirect' => 'home'
        ]);
    }

    public function forgotSendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Enter valid 10-digit mobile number',
            ], 422);
        }

        // 🔍 Check if user exists
        $user = DB::table('customer_users')
            ->where('number', $request->number)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Mobile number not registered',
            ]);
        }

        try {
            $otp = rand(100000, 999999);
            $number = '91' . $request->number;

            Cache::put('fp_otp_' . $request->number, $otp, now()->addMinutes(2));
            Session::put('fp_otp_' . $request->number, $otp);
            Session::put('fp_otp_expiry_' . $request->number, now()->addMinutes(2));

            $smsConfig = config('services.smswala');
            $msg = "Password reset OTP: {#var#}. Valid for {#var#} minutes. Bulk Basket India";
            $finalMsg = str_replace(
                ['{#var#}', '{#var#}'],
                [$otp, 2],
                $msg
            );

            $url = "{$smsConfig['url']}?"
                . "key={$smsConfig['key']}"
                . "&campaign={$smsConfig['campaign']}"
                . "&routeid={$smsConfig['routeid']}"
                . "&type=text"
                . "&contacts={$number}"
                . "&senderid={$smsConfig['sender']}"
                . "&msg=" . urlencode($finalMsg)
                . "&template_id=1707176908109346170"
                . "&pe_id={$smsConfig['pe_id']}";
            $response = Http::get($url);
            if ($response->successful()) {
                Log::info("✅ Forgot OTP sent to {$number}, OTP={$otp}");
                return response()->json([
                    'status' => true,
                    'message' => 'OTP sent successfully',
                ]);
            }
            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP',
            ]);
        } catch (\Exception $e) {
            Log::error("🔥 Forgot OTP Error: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Server error',
            ]);
        }
    }

    public function forgotPWD(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number'   => 'required|digits:10',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ]);
        }
        $user = DB::table("customer_users as a")
            ->select("a.*", "b.active")
            ->join("customers as b", "a.customer_id", "b.id")
            ->where("a.number", $request->number)
            ->where("b.active", 1)
            ->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found']);
        }


        DB::table('customer_users')
            ->where('number', $user->number)
            ->update([
                'password' => $request->password,
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully'
        ]);
    }


    public function forgotVerifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid input',
            ], 422);
        }
        $storedOtp = Cache::get('fp_otp_' . $request->number);
        $expiry = Session::get('fp_otp_expiry_' . $request->number);
        Log::info("🔍 Forgot OTP Check", [
            'number' => $request->number,
            'entered' => $request->otp,
            'stored' => $storedOtp,
            'expiry' => $expiry
        ]);
        if (!$storedOtp) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired. Please request again.',
            ]);
        }
        if ($storedOtp != $request->otp) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect OTP',
            ]);
        }
        Session::forget('fp_otp_' . $request->number);
        Session::forget('fp_otp_expiry_' . $request->number);
        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully',
        ]);
    }


    public function saveCustomerApi(Request $request)
    {
        $supplier_id = 1;
        $validator = Validator::make($request->all(), [
            'number' => 'required|digits:10',
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            $customer = DB::table("customers")->where("number", $request->number)->first();
            if ($customer) {
                DB::table('customers')->where("id", $customer->id)->update([
                    "customer_type" => $request->customer_type,
                    "type"          => $request->type,
                    "brand_name"          => $request->brand_name,
                    "name"  => $request->company_name,
                    "email" => $request->company_email,
                    "number" => $request->company_number,
                    "gst"           => $request->company_gst,
                    "address"       => $request->address,
                    "state"         => $request->state,
                    "city"          => $request->city,
                    "district"      => $request->district,
                    "pincode"       => $request->pincode,
                    "supplier_id"   => $supplier_id,
                    "active"        => 2,
                    "updated_at"    => now(),
                ]);
                DB::table('customer_users')->where("customer_id", $customer->id)->update([
                    "name"      => $request->name,
                    "number"    => $request->number,
                    "email"     => $request->email,
                    "password"  => $request->password,
                    "address"   => $request->address,
                    "state"     => $request->state,
                    "city"      => $request->city,
                    "district"  => $request->district,
                    "pincode"   => $request->pincode,
                    "updated_at" => now(),
                ]);
                $customer_id = $customer->id;
                $documents = DB::table('documents')->where('type', $request->type)->get();
                if ($documents->isNotEmpty()) {
                    $customerDocuments = $documents->map(function ($doc) use ($customer_id) {
                        return [
                            'customer_id' => $customer_id,
                            'type'        => $doc->type,
                            'name'        => $doc->name,
                        ];
                    })->toArray();
                    DB::table('customer_document')->insert($customerDocuments);
                }
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Your Account Under Process By Supplier Please Wait 2-3Hrs',
                    'customer_id' => $customer_id
                ], 201);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Number Already exists Please Another Number Try',
                ], 422);
            }
        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function customerLoginApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Number and password are required',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = DB::table("customer_users as a")
                ->select("a.*", "b.active")
                ->join("customers as b", "a.customer_id", "b.id")
                ->where("a.number", $request->number)
                ->where("a.password", $request->password)
                ->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Incorrect number or password Please Signup With Otp'
                ], 401);
            }

            if ($user->active == 2) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your Account is Under Process. Please contact your supplier.'
                ], 403);
            }

            if ($user->active == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is inactive. Please contact your supplier.'
                ], 403);
            }
            $token = bin2hex(random_bytes(16));
            $agent = new Agent();
            $browser = $agent->browser();
            $version = $agent->version($browser);
            $platform = $agent->platform();
            DB::table('customer_users')->where("id", $user->id)->update([
                'web_token' => $token,
                'last_ip' => $request->ip(),
                'last_login' => now(),
                'platform' => "$browser / $version / $platform"
            ]);

            DB::table('remember_token')->insert([
                'web_token' => $token,
                'user_id' =>   $user->id,
                'customer_id' =>   $user->customer_id,
                'last_ip' => $request->ip(),
                'last_login' => now(),
                'platform' => "$browser / $version / $platform"
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Server error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function apiLogout(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Token not provided.'
            ], 401);
        }
        $deleted = DB::table('remember_token')
            ->where('web_token', $token)
            ->delete();
        if (!$deleted) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid token.'
            ], 401);
        }
        return response()->json([
            'status' => true,
            'message' => 'Logout successful.'
        ], 200);
    }


}
