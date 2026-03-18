<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class customerMobileMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken(); // Gets token from Authorization header

        if (empty($token)) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized. Token missing.'
            ], 401);
        }

        try {
            $user = DB::table("customer_users as a")
                ->select("a.*", "b.supplier_id")
                ->join("customers as b", "a.customer_id", "b.id")
                ->where('a.app_token', $token)
                ->first();


            if (empty($user)) {
                return response()->json([
                    'error' => true,
                    'message' => 'Unauthorized. Invalid token.',
                ], 401);
            }

            $request->merge([
                "user" => (array)$user,

            ]);


            return $next($request);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage()
            ], 401);
        }
    }
}
