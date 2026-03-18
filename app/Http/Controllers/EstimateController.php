<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstimateController extends Controller
{
    public function createEstimate(Request $request){

   $data= DB::table('customers')->where("supplier_id",$request->user["supplier_id"])->where("active",1)->get();
    return view("suppliers.create-estimate",compact("data"));
    }
}
