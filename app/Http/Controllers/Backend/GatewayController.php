<?php

namespace App\Http\Controllers\Backend;

use App\Enums\GatewayType;
use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Traits\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\MoncashService;

class GatewayController extends Controller
{
    use ImageUpload;

    public function __construct()
    {
        $this->middleware('permission:automatic-gateway-manage', ['only' => ['automatic','update','testMoncash']]);
    }

    public function automatic(Request $request)
    {
        $gateways = Gateway::when($request->search != null,function($query){
            $query->where('name','LIKE','%'.request('search').'%');
        })->get();

        return view('backend.automatic_gateway.index', compact('gateways'));
    }

    public function update($id, Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'status' => 'required',
            'credentials' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back();
        }

        $gateway = Gateway::find($id);

        $user = \Auth::user();
        if ($gateway->type == GatewayType::Automatic) {
            if (! $user->can('automatic-gateway-manage')) {
                return redirect()->route('admin.gateway.automatic');
            }

        } else {
            if (! $user->can('manual-gateway-manage')) {
                return redirect()->route('admin.gateway.manual');
            }
        }

        $data = [
            'name' => $input['name'],
            'status' => $input['status'],
            'credentials' => json_encode($input['credentials']),
        ];

        if ($request->hasFile('logo')) {
            $logo = self::imageUploadTrait($input['logo'], $gateway->logo);
            $data = array_merge($data, ['logo' => $logo]);
        }

        $gateway->update($data);
        notify()->success($gateway->name.' '.__(' gateway updated successfully!'));

        return redirect()->route('admin.gateway.automatic');

    }

    public function testMoncash($id)
    {
        $gateway = Gateway::findOrFail($id);

        if (strtolower((string) $gateway->gateway_code) !== 'moncash') {
            notify()->error(__('Selected gateway is not MonCash.'), 'Error');

            return back();
        }

        try {
            $result = (new MoncashService())->testConnection();

            $message = __('MonCash connection successful. Mode: :mode | URL: :url', [
                'mode' => strtoupper((string) ($result['mode'] ?? 'production')),
                'url' => (string) ($result['base_url'] ?? ''),
            ]);

            notify()->success($message, 'Success');
        } catch (\Throwable $exception) {
            notify()->error(__('MonCash connection failed: :message', ['message' => $exception->getMessage()]), 'Error');
        }

        return back();
    }

    public function gatewayCurrency($gateway_id)
    {

        $gateway = Gateway::find($gateway_id);
        $supportedCurrencies = $gateway->supported_currencies;

        return [
            'view' => view('backend.automatic_gateway.include.__supported_currency', compact('supportedCurrencies'))->render(),
            'pay_currency' => is_custom_rate($gateway->gateway_code),
        ];
    }
}
