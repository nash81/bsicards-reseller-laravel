<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\GeneralSetting;
use App\Traits\ImageUpload;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller {

    use ImageUpload;

    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('permission:site-setting|email-setting', ['only' => ['update']]);
        $this->middleware('permission:site-setting', ['only' => ['siteSetting', 'seoMeta']]);
        $this->middleware('permission:email-setting', ['only' => ['mailSetting']]);
    }

    /**
     * @return Application|Factory|View
     */
    public static function siteSetting() {
        return view('backend.setting.site_setting.index');
    }

    public static function cardsetting() {
        $general = GeneralSetting::first();
        return view('backend.setting.card', compact('general'));
    }

    public static function cardupdate(Request $request) {
        $request->validate([
            'bsi_publickey' => 'required|string|max:255',
            'bsi_secretkey' => 'required|string|max:255',
            'bsi_resellerkey' => 'required|string|max:255',
            'bsiissue_fee' => 'required|numeric|gt:0',
            'bsiload_fee' => 'required|numeric|gt:0',
            'digifee' => 'required|numeric|gt:0',
        ]);

        $general = GeneralSetting::first();

        $general->bsiissue_fee = $request->bsiissue_fee;
        $general->bsiload_fee = $request->bsiload_fee;
        $general->digifee = $request->digifee;
        $general->bsi_publickey = $request->bsi_publickey;
        $general->bsi_secretkey = $request->bsi_secretkey;
        $general->bsi_resellerkey = $request->bsi_resellerkey;
        $general->save();

        notify()->success(__('Virtual Card Details Updated'));

        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public static function mailSetting() {
        return view('backend.setting.mail');
    }

    public static function mailConnectionTest(Request $request) {

        try {
            Mail::raw('Testing SMTP connection successful', function ($message) use ($request) {
                $message->to($request->email);
            });

            notify()->success(__('SMTP connection test successful.'));

            return back();
        } catch (\Exception $e) {
            notify()->error('SMTP connection test failed: ' . $e->getMessage(), 'Error');

            return redirect()->back();
        }
    }

    //store any setting

    /**
     * @return RedirectResponse
     */
    public function update(Request $request) {

        if ($request->ajax()) {

            $path = Setting::get($request->get('name'));

            if (file_exists(base_path('assets/' . $path))) {
                @unlink(base_path('assets/' . $path));
            }

            return response()->json([
                        'success' => true
            ]);
        }

        if ($request->has('referral_rules')) {
            Setting::updateOrCreate([
                'name' => 'referral_rules',
                    ], [
                'val' => json_encode(array_values($request->get('referral_rules'))),
            ]);
        }

        $section = $request->section;
        $rules = Setting::getValidationRules($section);
        $data = $this->validate($request, $rules);
        try {
            $validSettings = array_keys($rules);
            foreach ($data as $key => $val) {

                if (in_array($key, $validSettings)) {
                    if ($request->hasFile($key)) {
                        $oldImage = Setting::get($key, $section);

                        $val = self::imageUploadTrait($val, $oldImage);
                    }

                    Setting::add($key, $val, Setting::getDataType($key, $section));
                }
            }

            notify()->success(__('Settings has been saved'), 'Success');

            return redirect()->back();
        } catch (Exception $e) {
            notify()->error('Sorry, something went wrong.', 'Error');

            return redirect()->back();
        }
    }

    public function seoMeta() {
        return view('backend.setting.seo-meta');
    }
}
