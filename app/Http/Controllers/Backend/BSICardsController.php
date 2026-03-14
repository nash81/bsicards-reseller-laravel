<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use BSICards\BSICardsClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class BSICardsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:site-setting');
    }

    public function balances(Request $request): View
    {
        return $this->renderResource('balances', $request);
    }

    public function mastercards(Request $request): View
    {
        return $this->renderResource('mastercards', $request);
    }

    public function visacards(Request $request): View
    {
        return $this->renderResource('visacards', $request);
    }

    public function digitalMastercards(Request $request): View
    {
        return $this->renderResource('digital_mastercards', $request);
    }

    public function deposits(Request $request): View
    {
        return $this->renderResource('deposits', $request);
    }

    public function transactions(Request $request): View
    {
        return $this->renderResource('transactions', $request);
    }

    private function renderResource(string $resource, Request $request): View
    {
        $config = $this->resources()[$resource];
        $response = $this->fetchSdkResponse($config['method']);

        $rawRows = $resource === 'balances'
            ? $this->normalizeBalanceRows($this->extractBalancePayload($response))
            : $this->extractRows($this->extractListPayload($response));

        $columns = $this->resolveColumns($rawRows, $config['columns']);
        $collection = $this->applySearchAndSort(collect($rawRows), $columns, $request);
        $rows = $this->paginate($collection, $request);

        $apiError = null;
        if ((int) ($response['code'] ?? 0) !== 200) {
            $apiError = $response['message'] ?? __('Unable to load BSI Cards data right now.');
        }

        return view($config['view'], [
            'pageTitle' => $config['title'],
            'tableTitle' => $config['table_title'],
            'rows' => $rows,
            'columns' => $columns,
            'apiError' => $apiError,
        ]);
    }

    private function resources(): array
    {
        return [
            'balances' => [
                'method' => 'getWalletBalance',
                'title' => __('BSICards Balances'),
                'table_title' => __('Wallet Balances'),
                'view' => 'backend.bsicards.balances',
                'columns' => [
                    ['label' => 'Metric', 'field' => 'metric'],
                    ['label' => 'Value', 'field' => 'value'],
                ],
            ],
            'mastercards' => [
                'method' => 'getAllMastercards',
                'title' => __('BSICards Mastercards'),
                'table_title' => __('All Mastercards'),
                'view' => 'backend.bsicards.mastercards',
                'columns' => $this->cardColumns(),
            ],
            'visacards' => [
                'method' => 'getAllVisaCards',
                'title' => __('BSICards Visacards'),
                'table_title' => __('All Visacards'),
                'view' => 'backend.bsicards.visacards',
                'columns' => $this->cardColumns(),
            ],
            'digital_mastercards' => [
                'method' => 'getAllDigitalCards',
                'title' => __('BSICards Digital Mastercards'),
                'table_title' => __('All Digital Mastercards'),
                'view' => 'backend.bsicards.digital_mastercards',
                'columns' => $this->cardColumns(),
            ],
            'deposits' => [
                'method' => 'getDeposits',
                'title' => __('BSICards Deposits'),
                'table_title' => __('All Deposits'),
                'view' => 'backend.bsicards.deposits',
                'columns' => [
                    ['label' => 'ID', 'field' => 'id'],
                    ['label' => 'Reference', 'field' => 'reference'],
                    ['label' => 'User Email', 'field' => 'useremail'],
                    ['label' => 'Card ID', 'field' => 'cardid'],
                    ['label' => 'Amount', 'field' => 'amount'],
                    ['label' => 'Status', 'field' => 'status'],
                    ['label' => 'Created At', 'field' => 'created_at'],
                ],
            ],
            'transactions' => [
                'method' => 'getTransactions',
                'title' => __('BSICards Transactions'),
                'table_title' => __('All Transactions'),
                'view' => 'backend.bsicards.transactions',
                'columns' => [
                    ['label' => 'Created_at', 'field' => 'created_at'],
                    ['label' => 'Details', 'field' => 'details'],
                    ['label' => 'Tx Ref', 'field' => 'tx_ref'],
                    ['label' => 'Type', 'field' => 'type'],
                    ['label' => 'Amount', 'field' => 'amount'],
                    ['label' => 'Balance', 'field' => 'balance'],
                ],
            ],
        ];
    }

    private function cardColumns(): array
    {
        return [
            ['label' => 'Card ID', 'field' => 'cardid'],
            ['label' => 'User Email', 'field' => 'useremail'],
            ['label' => 'Name on Card', 'field' => 'nameoncard'],
            ['label' => 'Brand', 'field' => 'brand'],
            ['label' => 'Last 4', 'field' => 'lastfour'],
            ['label' => 'Available Balance', 'field' => 'available_balance'],
            ['label' => 'Status', 'field' => 'status'],
            ['label' => 'Created At', 'field' => 'created_at'],
        ];
    }

    private function fetchSdkResponse(string $method): array
    {
        $sdkResponse = [];

        try {
            $sdkResponse = $this->makeClient()->{$method}();

            if ($this->isSuccessResponse($sdkResponse)) {
                return $sdkResponse;
            }
        } catch (Throwable $exception) {
            $sdkResponse = [
                'code' => 500,
                'message' => $exception->getMessage(),
                'data' => [],
            ];
        }

        // Fallback for APIs that may reject the SDK's method shape in some environments.
        $fallback = $this->fetchViaHttpFallback($method);

        if ($this->isSuccessResponse($fallback)) {
            return $fallback;
        }

        return $sdkResponse ?: $fallback;
    }

    private function makeClient(): BSICardsClient
    {
        $general = GeneralSetting::query()->first(['bsi_publickey', 'bsi_secretkey']);

        $publicKey = trim((string) ($general->bsi_publickey ?? ''));
        $secretKey = trim((string) ($general->bsi_secretkey ?? ''));

        if (! $general || blank($publicKey) || blank($secretKey)) {
            throw new \RuntimeException(__('BSI Cards API keys are not configured. Please update them under Cardsetting first.'));
        }

        return new BSICardsClient($publicKey, $secretKey);
    }

    private function isSuccessResponse(array $response): bool
    {
        return (int) ($response['code'] ?? 0) === 200;
    }

    private function fetchViaHttpFallback(string $method): array
    {
        $routeMap = [
            'getWalletBalance' => 'admin/balance',
            'getAllMastercards' => 'admin/mastercards',
            'getAllVisaCards' => 'admin/visacards',
            'getAllDigitalCards' => 'admin/digitalcards',
            'getDeposits' => 'admin/deposits',
            'getTransactions' => 'admin/transactions',
        ];

        $endpoint = $routeMap[$method] ?? null;
        if (! $endpoint) {
            return [
                'code' => 500,
                'message' => __('Unsupported BSICards admin method: :method', ['method' => $method]),
                'data' => [],
            ];
        }

        $general = GeneralSetting::query()->first(['bsi_publickey', 'bsi_secretkey', 'bsi_resellerkey']);

        $publicKey = trim((string) ($general->bsi_publickey ?? ''));
        $secretKey = trim((string) ($general->bsi_secretkey ?? ''));
        $resellerKey = trim((string) ($general->bsi_resellerkey ?? ''));

        if (blank($publicKey) || blank($secretKey)) {
            return [
                'code' => 500,
                'message' => __('BSI Cards API keys are not configured. Please update them under Cardsetting first.'),
                'data' => [],
            ];
        }

        $baseUrl = bsi_merchant_api_url();
        $headers = [
            'publickey' => $publicKey,
            'secretkey' => $secretKey,
            'Accept' => 'application/json',
        ];

        if ($resellerKey !== '') {
            $headers['resellerkey'] = $resellerKey;
        }

        $client = Http::withHeaders($headers)->timeout(30)->connectTimeout(10);

        $getResponse = $client->get($baseUrl . $endpoint);
        $getPayload = $getResponse->json();

        if ($getResponse->ok() && is_array($getPayload) && $this->isSuccessResponse($getPayload)) {
            return $getPayload;
        }

        $postResponse = $client->post($baseUrl . $endpoint, []);
        $postPayload = $postResponse->json();

        if ($postResponse->ok() && is_array($postPayload) && $this->isSuccessResponse($postPayload)) {
            return $postPayload;
        }

        return [
            'code' => (int) ($postPayload['code'] ?? $getPayload['code'] ?? 500),
            'message' => (string) ($postPayload['message']
                ?? $getPayload['message']
                ?? ('HTTP fallback failed. GET[' . $getResponse->status() . '] POST[' . $postResponse->status() . ']')),
            'data' => $postPayload['data'] ?? $getPayload['data'] ?? [],
        ];
    }

    private function normalizeBalanceRows(mixed $payload): array
    {
        $data = $this->normalizePayload($payload);

        if (! is_array($data) || $data === []) {
            return [];
        }

        if (array_is_list($data)) {
            return array_map(fn ($row) => $this->normalizePayload($row), $data);
        }

        $nestedRows = $this->extractRows($data);
        if (count($nestedRows) > 1 || (count($nestedRows) === 1 && array_key_exists('metric', $nestedRows[0]))) {
            return $nestedRows;
        }

        $rows = [];
        foreach ($data as $key => $value) {
            if (in_array((string) $key, ['code', 'status', 'message'], true)) {
                continue;
            }

            if (is_array($value) || is_object($value)) {
                continue;
            }

            $rows[] = [
                'metric' => Str::headline((string) $key),
                'value' => $this->stringifyValue($value),
            ];
        }

        return $rows;
    }

    private function extractBalancePayload(array $response): mixed
    {
        if (array_key_exists('data', $response)) {
            return $response['data'];
        }

        return collect($response)
            ->except(['code', 'status', 'message'])
            ->all();
    }

    private function extractListPayload(array $response): mixed
    {
        if (array_key_exists('data', $response)) {
            return $response['data'];
        }

        return collect($response)
            ->except(['code', 'status', 'message'])
            ->all();
    }

    private function extractRows(mixed $payload, int $depth = 0): array
    {
        if ($depth > 6) {
            return [];
        }

        $data = $this->normalizePayload($payload);

        if (! is_array($data) || $data === []) {
            return [];
        }

        if (array_is_list($data)) {
            return array_values(array_map(fn ($row) => $this->normalizePayload($row), $data));
        }

        foreach (['data', 'response', 'records', 'results', 'items', 'transactions', 'cardTransactions', 'cards', 'mastercards', 'visacards', 'digitalcards', 'deposits'] as $key) {
            if (array_key_exists($key, $data)) {
                $rows = $this->extractRows($data[$key], $depth + 1);
                if ($rows !== []) {
                    return $rows;
                }
            }
        }

        foreach ($data as $value) {
            if (is_array($value) || is_object($value)) {
                $rows = $this->extractRows($value, $depth + 1);
                if ($rows !== []) {
                    return $rows;
                }
            }
        }

        return [$data];
    }

    private function resolveColumns(array $rows, array $preferredColumns): array
    {
        if ($rows === []) {
            return $preferredColumns;
        }

        $columns = collect($preferredColumns)
            ->filter(fn (array $column) => $this->rowsContainField($rows, $column['field']))
            ->values()
            ->all();

        if ($columns !== []) {
            return $columns;
        }

        $firstRow = collect($rows)->first(fn ($row) => is_array($row) && $row !== []);
        if (! is_array($firstRow)) {
            return $preferredColumns;
        }

        $fallbackColumns = [];
        foreach ($firstRow as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            $fallbackColumns[] = [
                'label' => Str::headline((string) $key),
                'field' => (string) $key,
            ];

            if (count($fallbackColumns) >= 8) {
                break;
            }
        }

        return $fallbackColumns !== [] ? $fallbackColumns : $preferredColumns;
    }

    private function rowsContainField(array $rows, string $field): bool
    {
        foreach ($rows as $row) {
            if (data_get($row, $field, '__missing__') !== '__missing__') {
                return true;
            }
        }

        return false;
    }

    private function applySearchAndSort(Collection $rows, array $columns, Request $request): Collection
    {
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $rows = $rows->filter(function ($row) use ($search) {
                return Str::contains(
                    Str::lower(implode(' ', $this->flattenValues($row))),
                    Str::lower($search)
                );
            })->values();
        }

        $allowedSortFields = collect($columns)->pluck('field')->all();
        $sortField = $request->input('sort_field');
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        if (in_array($sortField, $allowedSortFields, true)) {
            $rows = $rows->sortBy(
                fn ($row) => $this->sortableValue(data_get($row, $sortField)),
                SORT_NATURAL,
                $sortDir === 'desc'
            )->values();
        }

        return $rows;
    }

    private function paginate(Collection $rows, Request $request): LengthAwarePaginator
    {
        $allowedPerPage = [15, 30, 45, 60];
        $perPage = (int) $request->input('perPage', 15);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 15;
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function normalizePayload(mixed $payload): mixed
    {
        if (is_object($payload)) {
            return json_decode(json_encode($payload, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        }

        if (is_array($payload)) {
            return array_map(fn ($value) => $this->normalizePayload($value), $payload);
        }

        return $payload;
    }

    private function flattenValues(mixed $value): array
    {
        $value = $this->normalizePayload($value);

        if (is_array($value)) {
            return collect($value)
                ->flatMap(fn ($item) => $this->flattenValues($item))
                ->filter(fn ($item) => $item !== '')
                ->values()
                ->all();
        }

        return [$this->stringifyValue($value)];
    }

    private function sortableValue(mixed $value): int|float|string
    {
        $value = $this->normalizePayload($value);

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return Str::lower($this->stringifyValue($value));
    }

    private function stringifyValue(mixed $value): string
    {
        $value = $this->normalizePayload($value);

        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return collect($value)
                ->flatMap(fn ($item) => $this->flattenValues($item))
                ->implode(', ');
        }

        return (string) $value;
    }
}

