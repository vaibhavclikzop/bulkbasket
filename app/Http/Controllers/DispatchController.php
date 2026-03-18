<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Validator;

class DispatchController extends Controller
{

    public function ModeOfTransport(Request $request)
    {
        $data = DB::table("mode_of_transport")->get();
        return view("suppliers.mode-of-transport", compact("data"));
    }

    public function saveModeOfTransport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'number' => 'required',
            'vehicle_no' => 'required',
            'vehicle_name' => 'required',
            'user_name' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $count = 0;
            foreach ($messages->all() as $error) {
                if ($count == 0)
                    return redirect()->back()->with('error', $error);
                $count++;
            }
        }
        try {
            if (empty($request->id)) {
                DB::table('mode_of_transport')->insertGetId(array(
                    "name" => $request->name,
                    "number" => $request->number,
                    "vehicle_no" => $request->vehicle_no,
                    "vehicle_name" => $request->vehicle_name,
                    "user_name" => $request->user_name,
                    "password" => $request->password,
                    "supplier_id" => $request->user['supplier_id'],
                ));
            } else {
                DB::table('mode_of_transport')->where("id", $request->id)->update(array(
                    "name" => $request->name,
                    "number" => $request->number,
                    "vehicle_no" => $request->vehicle_no,
                    "vehicle_name" => $request->vehicle_name,
                    "user_name" => $request->user_name,
                    "password" => $request->password,
                ));
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        return  redirect()->back()->with("success", "Save Successfully");
    }


    public function dispatchPlan()
    {
        return view('suppliers.dispatch-plan');
    }
}
