<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\Transaction;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Txn;

class CardController extends Controller
{
    public function fees()
    {
        $general = GeneralSetting::first();

        return response()->json([
            'status' => true,
            'data'   => [
                'bsiissue_fee' => (float) ($general->bsiissue_fee ?? 0),
                'bsiload_fee'  => (float) ($general->bsiload_fee ?? 0),
                'digifee'      => (float) ($general->digifee ?? 0),
            ],
        ]);
    }

    // -------------------------------------------------------
    // Shared helper – extract a list from a BSI response,
    // trying the most common response shapes in order.
    // -------------------------------------------------------
    private function extractList(?object $response, string $debugTag = ''): array
    {
        if ($response === null) {
            \Log::warning("BSI [{$debugTag}] response is null / curl failed");
            return [];
        }

        \Log::debug("BSI [{$debugTag}] raw", ['body' => json_encode($response)]);

        // Shape 1 – { code: 200, data: [ ... ] }
        if (isset($response->code) && $response->code == 200) {
            $data = $response->data ?? null;

            if (is_array($data))  return $data;
            if (is_object($data)) {
                // Shape 2 – { code: 200, data: { cards: [...] } }
                foreach (['cards', 'data', 'result', 'list'] as $key) {
                    if (isset($data->$key) && is_array($data->$key)) {
                        return $data->$key;
                    }
                }
            }

            // Shape 3 – { code: 200, cards: [...] }   (data key missing)
            foreach (['cards', 'result', 'list'] as $key) {
                if (isset($response->$key) && is_array($response->$key)) {
                    return $response->$key;
                }
            }

            \Log::warning("BSI [{$debugTag}] code=200 but no recognisable list field",
                ['keys' => array_keys((array) $response)]);
            return [];
        }

        // Shape 4 – { status: true/200, data: [...] }
        if (isset($response->status) && ($response->status === true || $response->status == 200)) {
            $data = $response->data ?? null;
            if (is_array($data)) return $data;
        }

        \Log::warning("BSI [{$debugTag}] unexpected response shape",
            ['code' => $response->code ?? 'n/a', 'keys' => array_keys((array) $response)]);
        return [];
    }

    private function normalizePayload($payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (is_object($payload)) {
            return json_decode(json_encode($payload), true) ?: [];
        }

        return [];
    }

    private function normalizeDigitalCard(array $card): array
    {
        $type = strtolower((string) ($card['type'] ?? ''));

        if (! array_key_exists('isaddon', $card) && ! array_key_exists('is_addon', $card)) {
            $card['isaddon'] = $type === 'virtual-addon' ? 1 : 0;
        }

        return $card;
    }

    private function isSequentialArray(array $value): bool
    {
        return $value === [] || array_keys($value) === range(0, count($value) - 1);
    }

    private function extractArrayList(array $payload, array $keys): array
    {
        foreach ($keys as $key) {
            $value = $payload[$key] ?? null;

            if (is_array($value) && $this->isSequentialArray($value)) {
                return $value;
            }
        }

        return [];
    }

    private function extractDigitalTransactions(array $card): array
    {
        $transactions = $card['transactions'] ?? null;

        if (is_array($transactions) && $this->isSequentialArray($transactions)) {
            return $transactions;
        }

        if (! is_array($transactions)) {
            return [];
        }

        $paths = [
            ['response', 'items'],
            ['response', 'data', 'cardTransactions'],
            ['response', 'data', 'transactions'],
            ['data', 'cardTransactions'],
            ['data', 'transactions'],
            ['items'],
            ['cardTransactions'],
            ['transactions'],
        ];

        foreach ($paths as $path) {
            $value = $transactions;

            foreach ($path as $segment) {
                if (! is_array($value) || ! array_key_exists($segment, $value)) {
                    $value = null;
                    break;
                }

                $value = $value[$segment];
            }

            if (is_array($value) && $this->isSequentialArray($value)) {
                return $value;
            }
        }

        return [];
    }

    // -------------------------------------------------------
    // Shared helper – BSI API call
    // -------------------------------------------------------
    private function bsiCall(string $endpoint, array $body, GeneralSetting $general): ?object
    {
        $curl = curl_init();
        $data = json_encode($body);
        curl_setopt_array($curl, [
            CURLOPT_URL            => bsi_merchant_api_url($endpoint),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_HTTPHEADER     => [
                'publickey: ' . $general->bsi_publickey,
                'secretkey: ' . $general->bsi_secretkey,
                'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($curl);
        $curlErrNo = curl_errno($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            \Log::error('BSI cURL transport failure', [
                'endpoint' => $endpoint,
                'payload' => $body,
                'curl_errno' => $curlErrNo,
                'curl_error' => $curlError,
                'http_code' => $httpCode,
            ]);

            return (object) [
                'code' => 0,
                'status' => 'error',
                'message' => $curlError ?: 'BSI request failed before receiving a response.',
                'http_code' => $httpCode,
            ];
        }

        $decoded = json_decode($response);
        if (json_last_error() !== JSON_ERROR_NONE || $decoded === null) {
            \Log::error('BSI non-JSON/empty response', [
                'endpoint' => $endpoint,
                'payload' => $body,
                'http_code' => $httpCode,
                'json_error' => json_last_error_msg(),
                'raw_response' => is_string($response) ? mb_substr($response, 0, 2000) : null,
            ]);

            return (object) [
                'code' => $httpCode ?: 0,
                'status' => 'error',
                'message' => 'BSI returned an invalid response.',
                'raw_response' => is_string($response) ? mb_substr($response, 0, 500) : null,
            ];
        }

        return $decoded;
    }

    // =======================================================
    // MASTERCARD
    // =======================================================

    public function masterList(Request $request)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();

        $cards   = $this->bsiCall('getallcard', ['useremail' => $user->email], $general);
        $pending = $this->bsiCall('getpendingcards', ['useremail' => $user->email], $general);

        return response()->json([
            'status'  => true,
            'cards'   => $this->extractList($cards,   'getallcard'),
            'pending' => $this->extractList($pending, 'getpendingcards'),
        ]);
    }

    public function masterView(Request $request, string $cardId)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();

        $card  = $this->bsiCall('getcard', ['useremail' => $user->email, 'cardid' => $cardId], $general);
        $trans = $this->bsiCall('getcardtransactions', ['useremail' => $user->email, 'cardid' => $cardId], $general);

        if (! isset($card->code) || $card->code != 200) {
            return response()->json(['status' => false, 'message' => 'Card not found.'], 404);
        }

        return response()->json([
            'status'       => true,
            'card'         => $card->data ?? $card,
            'transactions' => $trans->data ?? [],
        ]);
    }

    public function masterLoadFunds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cardid' => 'required|string',
            'amount' => 'required|numeric|min:10',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user    = $request->user();
        $general = GeneralSetting::first();
        $fee     = round($request->amount * $general->bsiload_fee / 100, 2);
        $total   = $request->amount + $fee;

        if ($user->balance < $total) {
            return response()->json(['status' => false, 'message' => 'Insufficient balance.'], 422);
        }

        $user->balance -= $total;
        $user->save();

        $result = $this->bsiCall('fundcard', [
            'useremail' => $user->email,
            'cardid'    => $request->cardid,
            'amount'    => $request->amount,
        ], $general);

        if (isset($result->code) && $result->code == 200) {
            Txn::new($total, $fee, $total, 'BSICards', 'MasterCard Loaded for ' . $user->email, TxnType::Subtract, TxnStatus::Success, null, null, $user->id);
            return response()->json(['status' => true, 'message' => 'Funds loaded successfully.']);
        }

        $user->balance += $total;
        $user->save();
        return response()->json(['status' => false, 'message' => 'Failed to load funds. Please try again.'], 500);
    }

    public function masterApply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user    = $request->user();
        $general = GeneralSetting::first();
        $baseAmount = 10;
        $fee        = (float) ($general->bsiissue_fee ?? 0);
        $total      = $baseAmount + $fee;

        if ($user->balance < $total) {
            return response()->json(['status' => false, 'message' => 'Insufficient balance.'], 422);
        }

        $user->balance -= $total;
        $user->save();

        $result = $this->bsiCall('newcard', [
            'useremail'  => $user->email,
            'nameoncard' => trim($user->first_name . ' ' . $user->last_name),
            'pin'        => $request->pin,
        ], $general);

        if (isset($result->code) && $result->code == 200) {
            Txn::new($total, $fee, $total, 'BSICards', 'New Mastercard Fees', TxnType::Subtract, TxnStatus::Success, null, null, $user->id);
            return response()->json([
                'status'  => true,
                'message' => 'New Mastercard requested. Please allow 24-48 hours.',
                'data'    => $result->data ?? null,
            ]);
        }

        $user->balance += $total;
        $user->save();
        return response()->json(['status' => false, 'message' => $result->message ?? 'Error requesting new card.'], 500);
    }

    public function masterBlock(Request $request, string $cardId)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();
        $result  = $this->bsiCall('blockcard', ['useremail' => $user->email, 'cardid' => $cardId], $general);

        if (isset($result->code) && $result->code == 200) {
            return response()->json(['status' => true, 'message' => 'Card blocked successfully.']);
        }
        return response()->json(['status' => false, 'message' => 'Failed to block card.'], 500);
    }

    public function masterUnblock(Request $request, string $cardId)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();
        $result  = $this->bsiCall('unblockcard', ['useremail' => $user->email, 'cardid' => $cardId], $general);

        if (isset($result->code) && $result->code == 200) {
            return response()->json(['status' => true, 'message' => 'Card unblock requested.']);
        }
        return response()->json(['status' => false, 'message' => 'Failed to unblock card.'], 500);
    }

    // =======================================================
    // VISA CARD
    // =======================================================

    public function visaList(Request $request)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();

        $cards   = $this->bsiCall('visagetallcard',      ['useremail' => $user->email], $general);
        $pending = $this->bsiCall('visagetpendingcards', ['useremail' => $user->email], $general);

        return response()->json([
            'status'  => true,
            'cards'   => $this->extractList($cards,   'visagetallcard'),
            'pending' => $this->extractList($pending, 'visagetpendingcards'),
        ]);
    }

    public function visaView(Request $request, string $cardId)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();

        $card  = $this->bsiCall('visagetcard', ['useremail' => $user->email, 'cardid' => $cardId], $general);
        $trans = $this->bsiCall('visagetcardtransactions', ['useremail' => $user->email, 'cardid' => $cardId], $general);

        if (! isset($card->code) || $card->code != 200) {
            return response()->json(['status' => false, 'message' => 'Card not found.'], 404);
        }

        return response()->json([
            'status'       => true,
            'card'         => $card->data ?? $card,
            'transactions' => $trans->data ?? [],
        ]);
    }

    public function visaLoadFunds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cardid' => 'required|string',
            'amount' => 'required|numeric|min:10',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user    = $request->user();
        $general = GeneralSetting::first();
        $fee     = round($request->amount * ($general->bsiload_fee ?? 0) / 100, 2);
        $total   = $request->amount + $fee;

        if ($user->balance < $total) {
            return response()->json(['status' => false, 'message' => 'Insufficient balance.'], 422);
        }

        $user->balance -= $total;
        $user->save();

        $result = $this->bsiCall('visafundcard', [
            'useremail' => $user->email,
            'cardid'    => $request->cardid,
            'amount'    => $request->amount,
        ], $general);

        if (isset($result->code) && $result->code == 200) {
            Txn::new($total, $fee, $total, 'BSICards', 'VisaCard Loaded for ' . $user->email, TxnType::Subtract, TxnStatus::Success, null, null, $user->id);
            return response()->json(['status' => true, 'message' => 'Visa card funded successfully.']);
        }

        $user->balance += $total;
        $user->save();
        return response()->json(['status' => false, 'message' => 'Failed to load funds.'], 500);
    }

    public function visaApply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin'              => 'required|numeric',
            'dob'              => 'required|string',
            'nationalidnumber' => 'required|string',
            'userphoto'        => 'nullable',
            'nationalidimage'  => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $fileValidator = Validator::make($request->all(), [
            'userphoto'       => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
            'nationalidimage' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
        ]);
        if ($fileValidator->fails()) {
            return response()->json(['status' => false, 'message' => $fileValidator->errors()->first()], 422);
        }

        $userPhotoUrl = $request->input('userphoto');
        $nationalIdImageUrl = $request->input('nationalidimage');

        if ($request->hasFile('userphoto')) {
            $path = $request->file('userphoto')->store('images', 'public');
            $userPhotoUrl = Storage::disk('public')->url($path);
        }

        if ($request->hasFile('nationalidimage')) {
            $path = $request->file('nationalidimage')->store('images', 'public');
            $nationalIdImageUrl = Storage::disk('public')->url($path);
        }

        if (empty($userPhotoUrl) || empty($nationalIdImageUrl)) {
            return response()->json(['status' => false, 'message' => 'Both user photo and national ID image are required.'], 422);
        }

        if (! $request->hasFile('userphoto') && ! filter_var($userPhotoUrl, FILTER_VALIDATE_URL)) {
            return response()->json(['status' => false, 'message' => 'User photo must be a valid URL or image file.'], 422);
        }

        if (! $request->hasFile('nationalidimage') && ! filter_var($nationalIdImageUrl, FILTER_VALIDATE_URL)) {
            return response()->json(['status' => false, 'message' => 'National ID image must be a valid URL or image file.'], 422);
        }

        $user    = $request->user();
        $general = GeneralSetting::first();
        $baseAmount = 10;
        $fee        = (float) ($general->bsiissue_fee ?? 0);
        $total      = $baseAmount + $fee;

        if ($user->balance < $total) {
            return response()->json(['status' => false, 'message' => 'Insufficient balance.'], 422);
        }

        $user->balance -= $total;
        $user->save();

        $result = $this->bsiCall('visanewcard', [
            'useremail'        => $user->email,
            'nameoncard'       => trim($user->first_name . ' ' . $user->last_name),
            'pin'              => $request->pin,
            'dob'              => $request->dob,
            'userphoto'        => $userPhotoUrl,
            'nationalidimage'  => $nationalIdImageUrl,
            'nationalidnumber' => $request->nationalidnumber,
        ], $general);

        if (isset($result->code) && $result->code == 200) {
            Txn::new($total, $fee, $total, 'BSICards', 'New Visacard Fees', TxnType::Subtract, TxnStatus::Success, null, null, $user->id);
            return response()->json([
                'status'  => true,
                'message' => 'New Visa card requested. Please allow 24-48 hours.',
                'data'    => $result->data ?? null,
            ]);
        }

        $user->balance += $total;
        $user->save();
        return response()->json(['status' => false, 'message' => $result->message ?? 'Error requesting new card.'], 500);
    }

    public function visaBlock(Request $request, string $cardId)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();
        $result  = $this->bsiCall('visablockcard', ['useremail' => $user->email, 'cardid' => $cardId], $general);

        if (isset($result->code) && $result->code == 200) {
            return response()->json(['status' => true, 'message' => 'Visa card blocked.']);
        }
        return response()->json(['status' => false, 'message' => 'Failed to block card.'], 500);
    }

    public function visaUnblock(Request $request, string $cardId)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();
        $result  = $this->bsiCall('visaunblockcard', ['useremail' => $user->email, 'cardid' => $cardId], $general);

        if (isset($result->code) && $result->code == 200) {
            return response()->json(['status' => true, 'message' => 'Visa card unblock requested.']);
        }
        return response()->json(['status' => false, 'message' => 'Failed to unblock card.'], 500);
    }

    // =======================================================
    // DIGITAL MASTERCARD
    // =======================================================

    public function digitalList(Request $request)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();

        $cards = $this->bsiCall('getalldigital', ['useremail' => $user->email], $general);
        $list = array_map(function ($rawCard) {
            return $this->normalizeDigitalCard($this->normalizePayload($rawCard));
        }, $this->extractList($cards, 'getalldigital'));

        return response()->json([
            'status' => true,
            'cards'  => $list,
        ]);
    }

    public function digitalView(Request $request, string $cardId)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();

        $card    = $this->bsiCall('getdigitalcard', ['useremail' => $user->email, 'cardid' => $cardId], $general);
        $check3ds= $this->bsiCall('check3ds',       ['useremail' => $user->email, 'cardid' => $cardId], $general);

        if (! isset($card->code) || $card->code != 200) {
            return response()->json(['status' => false, 'message' => 'Card not found.'], 404);
        }

        $cardData = $this->normalizeDigitalCard(
            $this->normalizePayload($card->data ?? $card)
        );

        return response()->json([
            'status'       => true,
            'card'         => $cardData,
            'transactions' => $this->extractDigitalTransactions($cardData),
            'deposits'     => $this->extractArrayList($cardData, ['deposits']),
            'points'       => $this->extractArrayList($cardData, ['points']),
            'addon'        => $this->extractArrayList($cardData, ['addoncard', 'addon']),
            'check3ds'     => $check3ds->data ?? null,
        ]);
    }

    public function digitalLoadFunds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cardid' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user    = $request->user();
        $general = GeneralSetting::first();
        $fee     = round($request->amount * ($general->digital_loadfee ?? 0) / 100, 2);
        $total   = $request->amount + $fee;

        if ($user->balance < $total) {
            return response()->json(['status' => false, 'message' => 'Insufficient balance.'], 422);
        }

        $user->balance -= $total;
        $user->save();

        $result = $this->bsiCall('digitalfundcard', [
            'useremail' => $user->email,
            'cardid'    => $request->cardid,
            'amount'    => $request->amount,
        ], $general);

        if (isset($result->code) && $result->code == 200) {
            Txn::new($total, $fee, $total, 'BSICards', 'Digital Mastercard Loaded for ' . $user->email, TxnType::Subtract, TxnStatus::Success, null, null, $user->id);
            return response()->json(['status' => true, 'message' => 'Digital card funded successfully.']);
        }

        $user->balance += $total;
        $user->save();
        return response()->json(['status' => false, 'message' => 'Failed to load funds.'], 500);
    }

    public function digitalBlock(Request $request, string $cardId)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();
        $result  = $this->bsiCall('blockdigital', ['useremail' => $user->email, 'cardid' => $cardId], $general);

        if (isset($result->code) && $result->code == 200) {
            return response()->json(['status' => true, 'message' => 'Digital card blocked.']);
        }
        return response()->json(['status' => false, 'message' => 'Failed to block card.'], 500);
    }

    public function digitalUnblock(Request $request, string $cardId)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();
        $result  = $this->bsiCall('unblockdigital', ['useremail' => $user->email, 'cardid' => $cardId], $general);

        if (isset($result->code) && $result->code == 200) {
            return response()->json(['status' => true, 'message' => 'Digital card unblock requested.']);
        }
        return response()->json(['status' => false, 'message' => 'Failed to unblock card.'], 500);
    }

    public function digitalApply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:100',
            'lastname'  => 'required|string|max:100',
            'address1'  => 'required|string',
            'city'      => 'required|string',
            'country'   => 'required|string',
            'state'     => 'required|string',
            'postalcode' => 'required|string',
            'countrycode' => 'required|string',
            'phone'     => 'required|string',
            'dob'       => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user    = $request->user();
        $general = GeneralSetting::first();
        $fee     = $general->digifee ?? 4.50;

        if ($user->balance < $fee) {
            return response()->json(['status' => false, 'message' => 'Insufficient balance. Fee required: $' . $fee], 422);
        }

        $user->balance -= $fee;
        $user->save();

        $body = array_merge($request->only(['useremail','firstname','lastname','address1','city','country','state','postalcode','countrycode','phone','dob']), [
            'useremail' => $user->email,
        ]);
        $result = $this->bsiCall('digitalnewvirtualcard', $body, $general);

        if (isset($result->code) && $result->code == 200) {
            Txn::new($fee, 0, $fee, 'BSICards', 'New Virtual Digital MasterCard Issuance For ' . $user->email, TxnType::Subtract, TxnStatus::Success, null, null, $user->id);
            return response()->json(['status' => true, 'message' => 'Digital card application submitted.', 'data' => $result->data ?? null]);
        }

        $providerMessage = null;
        if (is_object($result)) {
            $providerMessage = $result->message
                ?? ($result->error ?? null)
                ?? (isset($result->data) && is_object($result->data) ? ($result->data->message ?? null) : null)
                ?? (isset($result->response) && is_object($result->response) ? ($result->response->message ?? null) : null);
        }

        \Log::error('BSI digitalnewvirtualcard failed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'payload' => $body,
            'provider_response' => $result,
        ]);

        $user->balance += $fee;
        $user->save();
        return response()->json([
            'status' => false,
            'message' => $providerMessage ?: 'Failed to apply for card.',
        ], 500);
    }

    public function digitalAddon(Request $request)
    {
        $validator = Validator::make($request->all(), ['cardid' => 'required|string']);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user    = $request->user();
        $general = GeneralSetting::first();
        $fee     = $general->digifee ?? 4.50;

        if ($user->balance < $fee) {
            return response()->json(['status' => false, 'message' => 'Insufficient balance. Fee required: $' . $fee], 422);
        }

        $user->balance -= $fee;
        $user->save();

        $result = $this->bsiCall('createaddon', [
            'useremail' => $user->email,
            'cardid'    => $request->cardid,
        ], $general);

        if (isset($result->code) && $result->code == 200) {
            Txn::new($fee, 0, $fee, 'BSICards', 'New Virtual Digital MasterCard Addon Issuance For ' . $user->email, TxnType::Subtract, TxnStatus::Success, null, null, $user->id);
            return response()->json(['status' => true, 'message' => 'Addon card applied successfully.', 'data' => $result->data ?? null]);
        }

        $user->balance += $fee;
        $user->save();
        return response()->json(['status' => false, 'message' => $result->message ?? 'Failed to apply addon card.'], 500);
    }

    /**
     * Check pending 3DS transaction for a digital card
     */
    public function check3ds(Request $request, string $cardId)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();
        $result  = $this->bsiCall('check3ds', ['useremail' => $user->email, 'cardid' => $cardId], $general);

        return response()->json([
            'status' => true,
            'data'   => $result->data ?? null,
        ]);
    }

    /**
     * Check Google/Apple Pay wallet OTP
     */
    public function checkWalletOtp(Request $request, string $cardId)
    {
        $user    = $request->user();
        $general = GeneralSetting::first();
        $result  = $this->bsiCall('checkwallet', ['useremail' => $user->email, 'cardid' => $cardId], $general);

        return response()->json([
            'status' => true,
            'data'   => $result->data ?? null,
        ]);
    }

    /**
     * Approve a pending 3DS transaction
     */
    public function approve3ds(Request $request, string $cardId)
    {
        $validator = Validator::make($request->all(), [
            'eventId' => 'nullable|string',
            'eventid' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $eventId = $request->input('eventId', $request->input('eventid'));
        if (empty($eventId)) {
            return response()->json(['status' => false, 'message' => 'eventId is required.'], 422);
        }

        $user    = $request->user();
        $general = GeneralSetting::first();
        $result  = $this->bsiCall('approve3ds', [
            'useremail' => $user->email,
            'cardid'    => $cardId,
            'eventId'   => $eventId,
        ], $general);

        if (isset($result->code) && $result->code == 200) {
            return response()->json(['status' => true, 'message' => '3DS approved successfully.']);
        }
        return response()->json(['status' => false, 'message' => '3DS approval failed.'], 500);
    }
}

