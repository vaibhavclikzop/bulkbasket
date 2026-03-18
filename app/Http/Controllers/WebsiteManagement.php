<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Jenssegers\Agent\Agent;

class WebsiteManagement extends Controller
{
    public function Sliders(Request $request)
    {
        $data =  DB::table("sliders")->get();
        return view("admin.sliders", compact("data"));
    }

    public function SaveSlider(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'file' => 'required',

        ]);

        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move('sliders', $file);
        }
        try {
            $data = [
                "image" => $file,
                "heading1" => $request->heading1,
                "heading2" => $request->heading2,

            ];
            if ($request->id) {
                DB::table('sliders')->where("id", $request->id)->delete();
            } else {
                DB::table('sliders')->insert($data);
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function Sliders1(Request $request)
    {

        $data =  DB::table("sliders1")->orderBy("id", "desc")->get();

        return view("admin.sliders1", compact("data"));
    }

    public function SaveSlider1(Request $request)
    {


        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move('sliders', $file);
        }
        try {
            $data = [
                "image" => $file,
                "link" => $request->link,
            ];
            if ($request->id) {
                DB::table('sliders1')->where("id", $request->id)->delete();
            } else {
                DB::table('sliders1')->insert($data);
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function Sliders2(Request $request)
    {

        $data =  DB::table("sliders2")->orderBy("id", "desc")->get();

        return view("admin.sliders2", compact("data"));
    }

    public function SaveSlider2(Request $request)
    {


        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move('sliders', $file);
        }
        try {
            $data = [
                "image" => $file,
                "link" => $request->link,
            ];
            if ($request->id) {
                DB::table('sliders2')->where("id", $request->id)->delete();
            } else {
                DB::table('sliders2')->insert($data);
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function Sliders3(Request $request)
    {

        $data =  DB::table("sliders3")->orderBy("id", "desc")->get();

        return view("admin.sliders3", compact("data"));
    }

    public function SaveSlider3(Request $request)
    {
        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move('sliders', $file);
        }
        try {
            $data = [
                "image" => $file,
                "link" => $request->link,
            ];
            if ($request->id) {
                DB::table('sliders3')->where("id", $request->id)->delete();
            } else {
                DB::table('sliders3')->insert($data);
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function Sliders4(Request $request)
    {

        $data =  DB::table("sliders4")->orderBy("id", "desc")->get();

        return view("admin.sliders4", compact("data"));
    }

    public function SaveSlider4(Request $request)
    {
        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move('sliders', $file);
        }
        try {
            $data = [
                "image" => $file,
                "link" => $request->link,
            ];
            if ($request->id) {
                DB::table('sliders4')->where("id", $request->id)->delete();
            } else {
                DB::table('sliders4')->insert($data);
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function faqCategory(Request $request)
    {
        $data =   DB::table("faq_category")->get();
        return view("admin.faq-category", compact("data"));
    }

    public function faqSaveCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
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
        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move('faq-images', $file);
        } else {
            if ($request->id) {
                $faq_category =  DB::table("faq_category")->where("id", $request->id)->first();
                $file = $faq_category->image;
            }
        }

        try {
            if ($request->id) {
                DB::table('faq_category')->where("id", $request->id)->update(array(
                    "name" => $request->name,
                    "seq" => $request->seq,
                    "image" => $file,
                ));
            } else {
                DB::table('faq_category')->insertGetId(array(
                    "name" => $request->name,
                    "seq" => $request->seq,
                    "image" => $file,
                ));
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function faqMainList(Request $request)
    {
        $faq_category = DB::table("faq_category")->get();
        $data = DB::table("main_faq as f")
            ->join("faq_category as c", "f.faq_cat_id", "=", "c.id")
            ->select("f.*", "c.name as category_name")
            ->get();
        return view("admin.faq-main-list", compact("data", "faq_category"));
    }

    public function faqSaveMain(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'faq_cat_id' => 'required',
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
            if ($request->id) {
                DB::table('main_faq')->where("id", $request->id)->update(array(
                    "faq_cat_id" => $request->faq_cat_id,
                    "question" => $request->question,
                    "answer" => $request->answer,
                ));
            } else {
                DB::table('main_faq')->insertGetId(array(
                    "faq_cat_id" => $request->faq_cat_id,
                    "question" => $request->question,
                    "answer" => $request->answer,
                ));
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function qulityMainList(Request $request)
    {
        $data = DB::table("quality_step")->get();
        return view("admin.quality-list", compact("data"));
    }

    public function qulitySaveMain(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required',
            'answer' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move(public_path('quality-images'), $file);
        } else {
            if ($request->id) {
                $quality = DB::table("quality_step")->where("id", $request->id)->first();
                $file = $quality->image;
            }
        }
        try {
            if ($request->id) {
                DB::table('quality_step')->where("id", $request->id)->update([
                    "image"    => $file,
                    "question" => $request->question,
                    "answer"   => $request->answer,
                ]);
            } else {
                DB::table('quality_step')->insert([
                    "image"    => $file,
                    "question" => $request->question,
                    "answer"   => $request->answer,
                ]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        return redirect()->back()->with("success", "Save Successfully");
    }

    public function emailTempList(Request $request)
    {

        $data = DB::table('email_template')->where('is_active', 1)->get();
        return view('admin.email-temp-list', compact("data"));
    }

    public function editEmailTemp(Request $request, $id)
    {
        $data = DB::table('email_template')->where('id', $request->id)->first();
        $emailVar = DB::table("email_variable")->get();
        return view('admin.edit-email-temp', compact("data", "emailVar"));
    }

    public function SaveEmailTemplate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        DB::table('email_template')
            ->where('id', $id)
            ->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'updated_at' => now(),
            ]);
        return redirect()
            ->back()
            ->with('success', 'Email Template updated successfully!');
    }

    public function refundList(Request $request)
    {
        $refund = DB::table('pages')->where('id', 1)->first();
        $data = compact('refund');
        return view('admin.refund-policies')->with($data);
    }

    public function refundSaveMain(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);
        DB::table('pages')
            ->where('id', $id)
            ->update([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'updated_at' => now(),
            ]);
        return redirect()
            ->back()
            ->with('success', 'Data updated successfully!');
    }

    public function termList(Request $request)
    {
        $term = DB::table('pages')->where('id', 2)->first();
        $data = compact('term');
        return view('admin.terms-conditions')->with($data);
    }

    public function termSaveMain(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);
        DB::table('pages')
            ->where('id', $id)
            ->update([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'updated_at' => now(),
            ]);
        return redirect()
            ->back()
            ->with('success', 'Data updated successfully!');
    }

    public function privacyList(Request $request)
    {
        $privacy  = DB::table('pages')->where('id', 3)->first();
        $data = compact('privacy');
        return view('admin.privacy-policies')->with($data);
    }

    public function privacySaveMain(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);
        DB::table('pages')
            ->where('id', $id)
            ->update([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'updated_at' => now(),
            ]);
        return redirect()
            ->back()
            ->with('success', 'Data updated successfully!');
    }

    public function orderCancellation(Request $request)
    {
        $orderCancellation  = DB::table('pages')->where('id', 4)->first();
        $data = compact('orderCancellation');
        return view('admin.order-cancellation')->with($data);
    }

    public function orderCancellationSaveMain(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);
        DB::table('pages')
            ->where('id', $id)
            ->update([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'updated_at' => now(),
            ]);
        return redirect()
            ->back()
            ->with('success', 'Data updated successfully!');
    }

    public function orderDelivery(Request $request)
    {
        $orderDelivery  = DB::table('pages')->where('id', 5)->first();
        $data = compact('orderDelivery');
        return view('admin.order-delivery-list')->with($data);
    }

    public function orderDeliverySaveMain(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);
        DB::table('pages')
            ->where('id', $id)
            ->update([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'updated_at' => now(),
            ]);
        return redirect()
            ->back()
            ->with('success', 'Data updated successfully!');
    }

    public function orderReturn(Request $request)
    {
        $orderReturn  = DB::table('pages')->where('id', 6)->first();
        $data = compact('orderReturn');
        return view('admin.order-return')->with($data);
    }

    public function orderReturnSaveMain(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);
        DB::table('pages')
            ->where('id', $id)
            ->update([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'updated_at' => now(),
            ]);
        return redirect()
            ->back()
            ->with('success', 'Data updated successfully!');
    }
}
