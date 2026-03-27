<?php

namespace App\Http\Controllers\Backend;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class CardTransactionController extends Controller
{
    public function availableBalance()
    {
        $general = GeneralSetting::first();
        $publickey = $general->bsi_publickey;
        $secretkey = $general->bsi_secretkey;
        $endpoint = env('API_ENDPOINT') . '/merchantbalance';
        $response = Http::withHeaders([
            'publickey' => $publickey,
            'secretkey' => $secretkey
        ])->get($endpoint);
        $card_balance = 0;
        $visafund_balance = 0;
        if ($response->ok()) {
            $data = $response->json();
            if (isset($data['card_balance'])) {
                $card_balance = $data['card_balance'];
            }
            if (isset($data['visafund_balance'])) {
                $visafund_balance = $data['visafund_balance'];
            }
        }
        return view('backend.card_transactions.available_balance', compact('card_balance', 'visafund_balance'));
    }

    public function issuedCards(Request $request)
    {
        $general = GeneralSetting::first();
        $publickey = $general->bsi_publickey;
        $secretkey = $general->bsi_secretkey;
        $endpoint = env('API_ENDPOINT') . '/merchantcards';
        $response = Http::withHeaders([
            'publickey' => $publickey,
            'secretkey' => $secretkey
        ])->get($endpoint);
        $cards = collect();
        if ($response->ok()) {
            $data = $response->json();
            if (isset($data['cards'])) {
                $cards = collect($data['cards']);
            }
        }
        // Filtering
        $search = $request->input('search');
        if ($search) {
            $cards = $cards->filter(function ($card) use ($search) {
                return stripos($card['cardid'], $search) !== false
                    || stripos($card['useremail'], $search) !== false
                    || stripos($card['brand'], $search) !== false
                    || stripos($card['lastfour'], $search) !== false;
            });
        }
        // Pagination
        $perPage = (int) $request->input('perPage', 15);
        $page = $request->input('page', 1);
        $cards = $cards->values(); // reset keys for pagination
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $cards->forPage($page, $perPage),
            $cards->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        return view('backend.card_transactions.issued_cards', ['cards' => $paginated]);
    }

    public function cardTransactions(Request $request)
    {
        $general = GeneralSetting::first();
        $publickey = $general->bsi_publickey;
        $secretkey = $general->bsi_secretkey;
        $endpoint = env('API_ENDPOINT') . '/merchanttransactions';
        $response = Http::withHeaders([
            'publickey' => $publickey,
            'secretkey' => $secretkey
        ])->get($endpoint);
        $transactions = collect();
        if ($response->ok()) {
            $data = $response->json();
            if (isset($data['transactions'])) {
                $transactions = collect($data['transactions']);
            }
        }
        // Filtering
        $search = $request->input('search');
        if ($search) {
            $transactions = $transactions->filter(function ($txn) use ($search) {
                return stripos($txn['tx_ref'], $search) !== false
                    || stripos($txn['details'], $search) !== false
                    || stripos($txn['amount'], $search) !== false
                    || stripos($txn['balance'], $search) !== false
                    || stripos($txn['created_at'], $search) !== false;
            });
        }
        // Pagination
        $perPage = (int) $request->input('perPage', 15);
        $page = $request->input('page', 1);
        $transactions = $transactions->values(); // reset keys for pagination
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $transactions->forPage($page, $perPage),
            $transactions->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        return view('backend.card_transactions.card_transactions', ['transactions' => $paginated]);
    }
}
