<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\DB;

class checkGST
{
    public function generate($gstNo)
    {


        try {
            $response = Http::post("https://commonapi.mastersindia.co/oauth/access_token", [
                "username" => "shubhamgoyal@bulkbasketindia.com",
                "grant_type" => "password",
                "password" => "Basketindia@123",
                "client_id" => "IsFowZLGfhMbzTRfIk",
                "client_secret" => "nGoe8NELkE1zNM0MfmdZrIvD"
            ]);
          
            
            if (!$response->successful()) {

                return response()->json([
                    'status' => false,
                    'error' => true,
                    'message' =>  $response->json() ?? $response->body()

                ], 500);
            }

            $data = $response->json();

            if (!isset($data['access_token'])) {

                return response()->json([
                    'status' => false,
                    'error' => true,
                    'message' => 'Invalid API response',

                ], 500);
            }
            $token = $data['access_token'];
            return  $this->getGSTDetails($token, $gstNo);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'error' => true
            ], 500);
        }
    }

    private function getGSTDetails($token, $gstNo)
    {



        try {

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'client_id' => 'IsFowZLGfhMbzTRfIk',
            ])->get('https://commonapi.mastersindia.co/commonapis/searchgstin', [
                'gstin' => $gstNo
            ]);
            $data = $response->json();

            if (!$response->successful()) {
                return response()->json([
                    'error' => true,
                    'status' => false,
                    'message' => $response->json()

                ], 500);
            }

            if ($response->successful()) {
                if ($data["error"] == true) {
                    return response()->json([
                        'error' => true,
                        'status' => false,
                        'message' =>  $data["message"]

                    ], 500);
                }
                return response()->json([
                    'error' => false,
                    'status' => true,
                    'message' => 'Successful',
                    'data' => $data
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'status' => false,
                'message' => 'API Error',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
