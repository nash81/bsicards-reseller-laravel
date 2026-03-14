<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Transaction;
use App\Http\Helpers\helpers;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator;

class VirtualCardController extends Controller {

    public function mastervirtual() {
        $pageTitle = 'Virtual MasterCard';
        $user = auth()->user();
        $general = GeneralSetting::first();
        $currency = setting('currency_symbol', 'global');
        $general->cur_txt = setting('currency_symbol', 'global');
        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('getallcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('getpendingcards'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $res = curl_exec($curl);

        curl_close($curl);
        $pendingcards = json_decode($res);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {

            return view('frontend.default.user.virtualcard.mastervirtual', compact('pageTitle', 'virtualcards', 'user', 'general', 'pendingcards', 'currency'));
        } else {
            $notify[] = ['error', 'Error Fetching Cards, Try Again Later'];
            return back()->withNotify($notify)->withInput();
        }
    }

    public function mastervirtualview($id) {

        $pageTitle = 'Virtual MasterCard';
        $user = auth()->user();
        $general = GeneralSetting::first();
        $currency = setting('currency_symbol', 'global');
        $general->cur_txt = setting('currency_symbol', 'global');
        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('getcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('getcardtransactions'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $res = curl_exec($curl);

        curl_close($curl);
        $decodedTransactions = json_decode($res);

        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            return view('frontend.default.user.virtualcard.showVirtualCard', compact('pageTitle', 'virtualcards', 'user', 'general', 'decodedTransactions'));
        } else {
            notify()->error(__('Error Fetching Card Details'), 'Error');

            return back();
        }
    }

    public function mastervirtualloadfunds(Request $request) {
        $user = auth()->user();
        $general = GeneralSetting::first();
        $currency = setting('currency_symbol', 'global');
        $general->cur_txt = setting('currency_symbol', 'global');
        $trx = getTrx();
        $this->validate($request, [
            'amount' => 'required|numeric|gt:9',
            'cardid' => 'required'
        ]);

        $fee = round($request->amount * $general->bsiload_fee / 100, 2);
        $totalamount = $request->amount + $fee;
        if ($user->balance < $totalamount) {

            notify()->error(__('Insufficient Balance'), 'Error');

            return back();
        }

        $user->balance -= $totalamount;
        $user->save();

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $request->cardid,
            "amount" => $request->amount
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('fundcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $totalamount;
            $transaction->final_amount = $totalamount;
            $transaction->charge = 00;
            $transaction->type = 'subtract';
            $transaction->description = 'Loaded Funds to card ' . $request->cardid;
            $transaction->tnx = $trx;
            $transaction->status = 'success';
            $transaction->save();

            notify()->success(__('Fund Load Request Successful - 24-48Hours'), 'Success');

            return back();
        } else {
            $user->balance += $totalamount;
            $user->save();
            notify()->error(__('Fund Load Request Failed'), 'Error');

            return back();
        }
    }

    public function mastervirtualnew(Request $request) {
        $user = auth()->user();
        $general = GeneralSetting::first();
        $currency = setting('currency_symbol', 'global');
        $general->cur_txt = setting('currency_symbol', 'global');
        $trx = getTrx();
        $this->validate($request, [
            'pin' => 'required|numeric',
        ]);
        $totalamount = $general->bsiissue_fee + 10;
        if ($user->balance < $totalamount) {
            notify()->error(__('Insufficient Balance'), 'Error');

            return back();
        }
        $user->balance -= $totalamount;
        $user->save();

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "nameoncard" => $user->first_name . " " . $user->last_name,
            "pin" => $request->pin,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('newcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $totalamount;
            $transaction->final_amount = $totalamount;
            $transaction->charge = 00;
            $transaction->type = 'subtract';
            $transaction->description = 'New Mastercard Fees ';
            $transaction->tnx = $trx;
            $transaction->status = 'success';
            $transaction->save();
            notify()->success(__('New Mastercard Requested 24-48Hours'), 'Success');

            return back();
        } else {
            $user->balance += $totalamount;
            $user->save();
            notify()->error(__('Error Requesting New Card'), 'Error');

            return back();
        }
    }

    public function mastervirtualunblock($id) {
        $user = auth()->user();
        $general = GeneralSetting::first();

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('unblockcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            notify()->success(__('Card Unblocked'), 'Success');

            return back();
        } else {
            notify()->error(__('Error Unblocking Card'), 'Error');

            return back();
        }
    }

    public function mastervirtualblock($id) {
        $user = auth()->user();
        $general = GeneralSetting::first();

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('blockcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            notify()->success(__('Card Blocked'), 'Success');

            return back();
        } else {
            notify()->error(__('Error blocking Card'), 'Error');

            return back();
        }
    }

    public function visavirtual() {
        $pageTitle = 'Virtual VisaCard';
        $user = auth()->user();
        $general = GeneralSetting::first();
        $currency = setting('currency_symbol', 'global');
        $general->cur_txt = setting('currency_symbol', 'global');
        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('visagetallcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('visagetpendingcards'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $res = curl_exec($curl);

        curl_close($curl);
        $pendingcards = json_decode($res);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {

            return view('frontend.default.user.virtualcard.visavirtual', compact('pageTitle', 'virtualcards', 'user', 'general', 'pendingcards'));
        } else {

            notify()->error(__('Error Fetching Cards, Try Again Later'), 'Error');

            return back();
        }
    }

    public function visavirtualview($id) {

        $pageTitle = 'Virtual VisaCard';
        $user = auth()->user();
        $general = GeneralSetting::first();
        $currency = setting('currency_symbol', 'global');
        $general->cur_txt = setting('currency_symbol', 'global');
        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('visagetcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('visagetcardtransactions'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $res = curl_exec($curl);

        curl_close($curl);
        $decodedTransactions = json_decode($res);

        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            return view('frontend.default.user.virtualcard.visashowVirtualCard', compact('pageTitle', 'virtualcards', 'user', 'general', 'decodedTransactions'));
        } else {

            notify()->error(__('Error Fetching Card Details, Try Again Later'), 'Error');

            return back();
        }
    }

    public function visavirtualloadfunds(Request $request) {
        $user = auth()->user();
        $general = GeneralSetting::first();
        $currency = setting('currency_symbol', 'global');
        $general->cur_txt = setting('currency_symbol', 'global');
        $trx = getTrx();
        $this->validate($request, [
            'amount' => 'required|numeric|gt:9',
            'cardid' => 'required'
        ]);

        $fee = round($request->amount * $general->usbvisa_loadfee / 100, 2);
        $totalamount = $request->amount + $fee;
        if ($user->balance < $totalamount) {
            $notify[] = ['error', 'Insufficient Balance'];
            return back()->withNotify($notify)->withInput();
        }

        $user->balance -= $totalamount;
        $user->save();

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $request->cardid,
            "amount" => $request->amount
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('visafundcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $totalamount;
            $transaction->final_amount = $totalamount;
            $transaction->charge = 00;
            $transaction->type = 'subtract';
            $transaction->description = 'Loaded Funds to card ' . $request->cardid;
            $transaction->tnx = $trx;
            $transaction->status = 'success';
            $transaction->save();

            notify()->success(__('Fund Load Request Successful - 24-48Hours'), 'Success');

            return back();
        } else {
            $user->balance += $totalamount;
            $user->save();
            notify()->error(__('Fund Load Request Failed'), 'Error');

            return back();
        }
    }

    public function visavirtualnew(Request $request) {
        $user = auth()->user();
        $general = GeneralSetting::first();
        $currency = setting('currency_symbol', 'global');
        $general->cur_txt = setting('currency_symbol', 'global');
        $trx = getTrx();

        $totalamount = $general->usdvisa_fee + 10;
        if ($user->balance < $totalamount) {

            notify()->error(__('Insufficient Balance'), 'Error');

            return back();
        }
        $user->balance -= $totalamount;
        $user->save();

        $curl = curl_init();
        if ($request->file('userphoto')) {
            $userphoto = $request->file('userphoto')->store('images', 'public');
        }
        if ($request->file('nationalid')) {
            $nationalidimage = $request->file('nationalid')->store('images', 'public');
        }

        $body = array(
            "useremail" => $user->email,
            "nameoncard" => $user->first_name . " " . $user->last_name,
            "pin" => $request->pin,
            "dob" => $request->dob,
            "userphoto" => asset($userphoto),
            "nationalidimage" => asset($nationalidimage),
            "nationalidnumber" => isset($request->nationalidnumber) ? $request->nationalidnumber : null,
        );
        $data = json_encode($body, JSON_UNESCAPED_SLASHES);

        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('visanewcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);

        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $totalamount;
            $transaction->final_amount = $totalamount;
            $transaction->charge = 00;
            $transaction->type = 'subtract';
            $transaction->description = 'New Visacard Fees ';
            $transaction->tnx = $trx;
            $transaction->status = 'success';
            $transaction->save();

            notify()->success(__('New Visacard Requested 24-48hours'), 'Success');

            return back();
        } else {
            $user->balance += $totalamount;
            $user->save();
            if (isset($virtualcards->message)) {
                $message = $virtualcards->message;
            } else {
                $message = null;
            }
            notify()->error(__('Error Requesting New Card'), 'Error');

            return back();
        }
    }

    public function visavirtualunblock($id) {
        $user = auth()->user();
        $general = GeneralSetting::first();
        $currency = setting('currency_symbol', 'global');
        $general->cur_txt = setting('currency_symbol', 'global');

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('visaunblockcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {

            notify()->success(__('Card Unblock Requested - 10mins Lead Time'), 'Success');

            return back();
        } else {

            notify()->error(__('Error Unblocking Card, Try Again Later'), 'Error');

            return back();
        }
    }

    public function visavirtualblock($id) {
        $user = auth()->user();
        $general = GeneralSetting::first();
        $currency = setting('currency_symbol', 'global');
        $general->cur_txt = setting('currency_symbol', 'global');

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('visablockcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            notify()->success(__('Card Block Requested - 10mins Lead Time'), 'Success');

            return back();
        } else {
            notify()->error(__('Error Blocking Card, Try Again Later'), 'Error');

            return back();
        }
    }

    //BSI DIGITAL & PHYSICAL WALLET CARDS START HERE

    public function getalldigital() {
        $pageTitle = 'Gpay/Apple Pay MasterCard';
        $user = auth()->user();
        $general = GeneralSetting::first();

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('getalldigital'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            return view('frontend.default.user.virtualcard.digitalvirtual', compact('pageTitle', 'virtualcards', 'user', 'general'));
        } else {
            notify()->error(__('Error Fetching Cards, Try Again Later'), 'Error');

            return back();
        }
    }

    public function getdigitalcard($id) {
        $pageTitle = 'Gpay/Apple Pay MasterCard';
        $user = auth()->user();
        $general = GeneralSetting::first();

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('getdigitalcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);

        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('check3ds'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $r = curl_exec($curl);

        curl_close($curl);
        $check3ds = json_decode($r);

        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            return view('frontend.default.user.virtualcard.showdigitalvirtual', compact('pageTitle', 'virtualcards', 'check3ds', 'user', 'general', 'id'));
        } else {

            notify()->error(__('Error Fetching Cards, Try Again Later'), 'Error');

            return back();
        }
    }

    public function checkstatus($id) {
        $user = auth()->user();
        $general = GeneralSetting::first();
        $curl = curl_init();
        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('check3ds'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $r = curl_exec($curl);

        curl_close($curl);
        $check3ds = json_decode($r);
        if (isset($check3ds->data->merchantName)) {
            $id = '<p class="text-mute mb-1">3DS Approval</p>
                        <h5>' . $check3ds->data->merchantName . ' - ' . $check3ds->data->merchantCurrency . '' . $check3ds->data->merchantAmount . '</h5>

                        <a href="' . route('user.approve3ds', ['id' => $id, 'eventid' => $check3ds->data->eventId]) . '" class="btn btn-success mt-3">Approve</a>';

            print_r($id);
        } else {
            $id = '<p class="text-mute mb-1">3DS Approval</p><h5>No Transactions</h5><p class="text-mute mb-1">Do not click alternative options on waiting screen, else you will have to retry the transaction.</p>';

            print_r($id);
        }
    }

    public function checkotp($id) {
        $user = auth()->user();
        $general = GeneralSetting::first();
        $curl = curl_init();
        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('checkwallet'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $r = curl_exec($curl);

        curl_close($curl);
        $check3ds = json_decode($r);
        if (isset($check3ds->data->activationCode)) {
            $id = '<p class="text-mute mb-1">Wallet OTP</p>
                        <h5>OTP: ' . $check3ds->data->activationCode . '</h5>';
            print_r($id);
        } else {
            $id = '<p class="text-mute mb-1">Wallet OTP - Under Approval</p>
                        <h5>Use Email Verification While Adding Card To Your Gpay Wallet. OTP will display here</h5>';
            print_r($id);
        }
    }

    public function blockdigital($id) {
        $user = auth()->user();
        $general = GeneralSetting::first();

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('blockdigital'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            notify()->success(__('Card Block Requested'), 'Success');

            return back();
        } else {
            notify()->error(__('Error Blocking Card, Try Again Later'), 'Error');

            return back();
        }
    }

    public function unblockdigital($id) {
        $user = auth()->user();
        $general = GeneralSetting::first();

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('unblockdigital'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            notify()->success(__('Card UnBlock Requested'), 'Success');

            return back();
        } else {
            notify()->error(__('Error Unblocking Card, Try Again Later'), 'Error');

            return back();
        }
    }

    public function digitalnewvirtualcard(Request $request) {

        $this->validate($request, [
            'dob' => 'required',
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'phone' => 'required',
            'countrycode' => 'required',
            'postalcode' => 'required'
        ]);
        $user = auth()->user();
        $general = GeneralSetting::first();

        $curl = curl_init();

        if ($user->balance < $general->digifee) {
            notify()->error(__('Insufficient Balance'), 'Error');

            return back();
        }

        $user->balance -= $general->digifee;
        $user->save();

        $body = array(
            'firstname' => $user->first_name,
            'lastname' => $user->last_name,
            'dob' => $request->dob,
            'address1' => $request->address1,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            "useremail" => $user->email,
            "countrycode" => $request->countrycode,
            "phone" => $request->phone,
            'postalcode' => $request->postalcode,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('digitalnewvirtualcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $general->digifee;
            $transaction->final_amount = $general->digifee;

            $transaction->charge = 00;
            $transaction->type = 'subtract';
            $transaction->description = 'New Gpay/Apple Pay Mastercard Fees';
            $transaction->tnx = getTrx();
            $transaction->status = 'success';
            $transaction->save();

            $shortcodes = [
                '[[full_name]]' => $user->first_name . " " . $user->last_name,
            ];

            // Notify user and admin
          //  $this->pushNotify('new_card', $shortcodes, route('admin.physical'), $user->id, 'Admin');

            notify()->success(__('New Digital Mastercard Requested'), 'Success');
            return back();
        } else {
            $user->balance += $general->digifee;
            $user->save();

            notify()->error(__('Error issuing new card, Try Again Later'), 'Error');

            return back();
        }
    }

    public function approve3ds($id, $eventid) {
        $user = auth()->user();
        $general = GeneralSetting::first();

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $id,
            "eventId" => $eventid,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('approve3ds'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            notify()->success(__('Transaction Approved'), 'Success');
            return back();
        } else {

            notify()->error(__('Transaction Timed Out'), 'Error');

            return back();
        }
    }

    public function digitalloadfunds(Request $request) {
        $user = auth()->user();
        $general = GeneralSetting::first();
        $trx = getTrx();
        $this->validate($request, [
            'amount' => 'required|numeric|gt:4',
            'cardid' => 'required',
        ]);

        $fee = round($request->amount * $general->bsiload_fee / 100, 2) + 1;
        $totalamount = $request->amount + $fee;
        if ($user->balance < $totalamount) {
            notify()->error(__('Insufficient Balance'), 'Error');

            return back();
        }

        $user->balance -= $totalamount;
        $user->save();

        $curl = curl_init();

        $body = array(
            "useremail" => $user->email,
            "cardid" => $request->cardid,
            "amount" => $request->amount,
        );
        $data = json_encode($body);
        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('digitalfundcard'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $virtualcards = json_decode($response);
        if (isset($virtualcards->code) && $virtualcards->code == 200) {
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $totalamount;
            $transaction->final_amount = $totalamount;
            $transaction->charge = 00;
            $transaction->type = 'subtract';
            $transaction->description = 'Loaded Funds to Digital Mastercard ' . $request->cardid;
            $transaction->tnx = $trx;
            $transaction->status = 'success';
            $transaction->save();
            notify()->success(__('Fund Load Request Successful - 24-48Hours'), 'Success');

            return back();
        } else {
            $user->balance += $totalamount;
            $user->save();
            notify()->error(__('Fund Load Request Failed'), 'Error');

            return back();
        }
    }

    public function digitaladdoncard(Request $request) {
        $this->validate($request, [
            'cardid' => 'required|string',
        ]);

        $user = auth()->user();
        $general = GeneralSetting::first();
        $fee = (float) $general->digifee;

        if ($user->balance < $fee) {
            notify()->error(__('Insufficient Balance'), 'Error');
            return back();
        }

        $user->balance -= $fee;
        $user->save();

        $curl = curl_init();
        $body = array(
            'useremail' => $user->email,
            'cardid' => $request->cardid,
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => bsi_merchant_api_url('createaddon'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => array(
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        $hasCurlError = curl_errno($curl);
        curl_close($curl);

        $apiResponse = json_decode($response);
        if (!$hasCurlError && isset($apiResponse->code) && (int) $apiResponse->code === 200) {
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $fee;
            $transaction->final_amount = $fee;
            $transaction->charge = 0;
            $transaction->type = 'subtract';
            $transaction->description = 'Addon card issuance fee - ' . $request->cardid;
            $transaction->tnx = getTrx();
            $transaction->status = 'success';
            $transaction->save();

            notify()->success(__('Addon card request submitted successfully'), 'Success');
            return back();
        }

        $user->balance += $fee;
        $user->save();

        notify()->error(__('Addon card request failed. Your balance has been restored.'), 'Error');
        return back();
    }
}
