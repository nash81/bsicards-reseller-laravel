@extends('frontend::layouts.user')
@section('title')
    {{ __('MasterCard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="site-card">
                <div class="site-card-header">
                    <h3 class="title-small">{{ __('Gpay MasterCard') }}</h3>
                </div>
                <div class="site-card-body">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-4 col-xl-4">
                            <div class="virtual-bank-card digital-theme">
                                <div class="card-top">
                                    <span class="chip"></span>
                                    <span class="brand">GPAY DIGITAL</span>
                                </div>
                                <div class="card-number">{{ preg_replace('/(\d{4})(?=\d)/', '$1 ', $virtualcards->data->card_number ?? '') }}</div>
                                <div class="card-bottom">
                                    <div>
                                        <div class="label">{{ __('Card Holder') }}</div>
                                        <div class="value">{{ $virtualcards->data->nameoncard ?? ($user->first_name.' '.$user->last_name) }}</div>
                                    </div>
                                    <div>
                                        <div class="label">{{ __('Expires') }}</div>
                                        <div class="value">{{ $virtualcards->data->expiry_month ?? '--' }}/{{ $virtualcards->data->expiry_year ?? '--' }}</div>
                                    </div>
                                    <div>
                                        <div class="label">CVV</div>
                                        <div class="value">{{ $virtualcards->data->cvv ?? '***' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-block mt-3" id="resultDiv">
                                @if(isset($check3ds->data->merchantName))
                                    <div class="k">{{ __('3DS Approval') }}</div>
                                    <div class="v small">{{ __($check3ds->data->merchantName) }} - {{ __($check3ds->data->merchantCurrency) }} {{ __($check3ds->data->merchantAmount) }}</div>
                                    <div class="text-muted mt-1">{{ __('Time remaining') }}: <span id="timer">30</span>s</div>
                                    <a href="{{ route('user.approve3ds',['id'=>$virtualcards->data->cardid,'eventid'=>$check3ds->data->eventId]) }}" class="site-btn-sm primary-btn mt-2">{{ __('Approve') }}</a>
                                @else
                                    <div class="k">{{ __('3DS Approval') }}</div>
                                    <div class="text-muted">{{ __('No pending request') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-8 col-xl-8">
                            <div class="card-info-grid">
                                <div class="info-block">
                                    <div class="k">{{ __('Balance (USD)') }}</div>
                                    <div class="v">{{ $virtualcards->data->balance ?? '0.00' }}</div>
                                </div>
                                <div class="info-block" id="otpDiv"></div>
                                <div class="info-block full">
                                    <div class="k">{{ __('Billing Address') }}</div>
                                    <div class="v small">
                                        {{ $virtualcards->data->address1 ?? '' }}, {{ $virtualcards->data->city ?? '' }}, {{ $virtualcards->data->state ?? '' }},
                                        {{ $virtualcards->data->country ?? '' }}, {{ $virtualcards->data->postalCode ?? '' }}
                                    </div>
                                </div>
                                <div class="info-block">
                                    <div class="k">{{ __('Status') }}</div>
                                    <div class="v">
                                        @if(($virtualcards->data->status ?? '') == 'active')
                                            <span class="site-badge badge-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="site-badge badge-failed">{{ __('Blocked') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="info-block">
                                    <div class="k">{{ __('Network') }}</div>
                                    <div class="v">{{ ucfirst($virtualcards->data->brand ?? '') }} {{ ucfirst($virtualcards->data->type ?? '') }}</div>
                                </div>
                                @if(($virtualcards->data->pin ?? null) != null)
                                <div class="info-block">
                                    <div class="k">{{ __('Pin') }}</div>
                                    <div class="v">{{ __($virtualcards->data->pin) }}</div>
                                </div>
                                @endif
                                <div class="info-block">
                                    <div class="k">{{ __('Points') }}</div>
                                    <div class="v">{{ __($virtualcards->data->point_balance ?? '') }}</div>
                                </div>
                                <div class="info-block full">
                                    <div class="k">{{ __('Deposit Address') }}</div>
                                    <div class="info-actions mt-2">
                                        <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#qrcode">USDC <i class="fa fa-qrcode"></i></button>
                                        <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#usdtqrcode">USDT <i class="fa fa-qrcode"></i></button>
                                        <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#btcqrcode">BTC <i class="fa fa-qrcode"></i></button>
                                        <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#ethqrcode">ETH <i class="fa fa-qrcode"></i></button>
                                        <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#bnbqrcode">BNB <i class="fa fa-qrcode"></i></button>
                                        <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#xrpqrcode">XRP <i class="fa fa-qrcode"></i></button>
                                        <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#solqrcode">SOL <i class="fa fa-qrcode"></i></button>
                                        <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#paxgqrcode">PAXG <i class="fa fa-qrcode"></i></button>
                                    </div>
                                    <div class="text-danger mt-2" style="font-size: 12px;">{{ __('For non USDC coins deposit fees 2% plus network fees apply. Coins to USDC exchange determined at actual rates. USDC loading charge is 0%. Minimum $10 equivalent.') }}</div>
                                </div>
                                <div class="info-actions full">

                                    @if(($virtualcards->data->status ?? '') != 'active')
                                        <a href="{{ route('user.unblockdigital',$virtualcards->data->cardid) }}" class="site-btn-sm primary-btn">{{ __('Unblock') }}</a>
                                    @else
                                        <a href="{{ route('user.blockdigital',$virtualcards->data->cardid) }}" class="site-btn-sm red-btn" onclick="return confirm('{{ __('Are you sure you want to block the card?') }}')">{{ __('Block') }}</a>
                                    @endif
                                    @if((int) data_get($virtualcards, 'data.isaddon', 0) === 0)
                                        <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#addonCardModal">
                                            <i class="fa fa-plus"></i> {{ __('Addon card') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="site-card">
                <div class="site-card-header">
                    <h3 class="title-small">{{ __('Card Transaction History') }}</h3>
                </div>
                <div class="site-card-body">
                    @php
                        $isAddonCard = (int) data_get($virtualcards, 'data.isaddon', 0) === 1;
                    @endphp
                    <ul class="nav nav-tabs border-0" id="transactionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="existing-tab" data-bs-toggle="tab" data-bs-target="#existing" type="button" role="tab" aria-controls="existing" aria-selected="true">{{ __('Transactions') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sample-tab" data-bs-toggle="tab" data-bs-target="#sample" type="button" role="tab" aria-controls="sample" aria-selected="false">{{ __('Deposits') }}</button>
                        </li>

                        @if(!$isAddonCard)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="addon-tab" data-bs-toggle="tab" data-bs-target="#addon" type="button" role="tab" aria-controls="addon" aria-selected="false">{{ __('Addon') }}</button>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-content mt-3" id="transactionTabsContent">
                        <div class="tab-pane fade show active" id="existing" role="tabpanel" aria-labelledby="existing-tab">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($virtualcards->data->transactions->response->items as $transaction)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($transaction->paymentDateTime)->format('F d, Y H:i') }}<br>{{ ucfirst($transaction->status) }} - {{ ucfirst($transaction->declineReason ?? '') }}</td>
                                                <td>{{ ucfirst($transaction->type) }}</td>
                                                <td>{{ $transaction->merchant->name }}</td>
                                                <td>
                                                    @if($transaction->type == 'payment')
                                                        <span class="red-color">USD {{ number_format($transaction->amount, 2) }}</span>
                                                    @else
                                                        <span class="green-color">USD {{ number_format($transaction->amount, 2) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="sample" role="tabpanel" aria-labelledby="sample-tab">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Trx#') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($virtualcards->data->deposits as $d)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($d->createdAt)->format('F d, Y H:i') }}</td>
                                                <td>{{ __('Deposit') }}</td>
                                                <td><a href="https://polygonscan.com/tx/{{ $d->transactionHash }}" target="_blank">{{ $d->transactionHash }}</a></td>
                                                <td><span class="green-color">USDC {{ $d->amount / 1000000 }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="points" role="tabpanel" aria-labelledby="points-tab">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Details') }}</th>
                                            <th>{{ __('Points') }}</th>
                                            <th>{{ __('Balance') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($virtualcards->data->points as $p)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($p->created_at)->format('F d, Y H:i') }}</td>
                                                <td>{{ $p->details }}</td>
                                                <td>
                                                    @if($p->type == '-')
                                                        <span class="red-color">{{ $p->points }}</span>
                                                    @else
                                                        <span class="green-color">{{ $p->points }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $p->balance }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if(!$isAddonCard)
                        <div class="tab-pane fade" id="addon" role="tabpanel" aria-labelledby="addon-tab">
                            @php
                                $addons = data_get($virtualcards, 'data.addoncard', []);
                                $addons = is_array($addons) ? $addons : [];
                            @endphp

                            @if(!empty($addons))
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Card ID') }}</th>
                                                <th>{{ __('Last four') }}</th>
                                                <th class="text-end">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($addons as $addon)
                                                @php
                                                    $addonCardId = data_get($addon, 'cardid');
                                                    $addonStatus = data_get($addon, 'lastfour', '0000');
                                                @endphp
                                                <tr>
                                                    <td>{{ $addonCardId ?? '--' }}</td>
                                                    <td>{{ $addonStatus }}</td>
                                                    <td class="text-end">
                                                        @if($addonCardId)
                                                            <a href="{{ route('user.getdigitalcard', $addonCardId) }}" class="site-btn-sm primary-btn">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                        @else
                                                            <button type="button" class="site-btn-sm primary-btn" disabled>
                                                                <i class="fa fa-eye"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-muted">{{ __('No addon cards found') }}</div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Modals -->
    <div class="modal fade" id="qrcode">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header d-block">
                    <h5 class="modal-title">@lang('USDC POLYGON DEPOSIT ADDRESS')</h5>
                    <p class="fs-12">@lang('Funds load to your card in 20minutes')</p>
                </div>
                <div class="modal-body text-center">
                    @php $result = str_replace('USDC-POLYGON-', '', $virtualcards->data->depositaddress); @endphp
                    <img src="{{ 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $result }}" alt="QR code" />
                    <p>{{ __($virtualcards->data->depositaddress) }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="usdtqrcode"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header d-block"><h5 class="modal-title">@lang('USDT BSC/BEP20 DEPOSIT ADDRESS')</h5><p class="fs-12">@lang('Funds load to your card in 20minutes')</p></div><div class="modal-body text-center">@php $results = str_replace('USDT-BSC|BEP20-', '', $virtualcards->data->usdtdepositaddress); @endphp <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $results; ?>" alt="QR code" /><p>{{ __($virtualcards->data->usdtdepositaddress) }}</p></div></div></div></div>
    <div class="modal fade" id="btcqrcode"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header d-block"><h5 class="modal-title">@lang('BTC DEPOSIT ADDRESS')</h5><p class="fs-12">@lang('Funds load to your card in 20minutes')</p></div><div class="modal-body text-center">@php $resultss = str_replace('BTC-', '', $virtualcards->data->btcdepositaddress); @endphp <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $resultss; ?>" alt="QR code" /><p>{{ __($virtualcards->data->btcdepositaddress) }}</p></div></div></div></div>
    <div class="modal fade" id="ethqrcode"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header d-block"><h5 class="modal-title">@lang('ETH DEPOSIT ADDRESS')</h5><p class="fs-12">@lang('Funds load to your card in 20minutes')</p></div><div class="modal-body text-center">@php $resultsss = str_replace('ETH-', '', $virtualcards->data->ethdepositaddress); @endphp <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $resultsss; ?>" alt="QR code" /><p>{{ __($virtualcards->data->ethdepositaddress) }}</p></div></div></div></div>
    <div class="modal fade" id="bnbqrcode"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header d-block"><h5 class="modal-title">@lang('BNB DEPOSIT ADDRESS')</h5><p class="fs-12">@lang('Funds load to your card in 20minutes')</p></div><div class="modal-body text-center">@php $bnbresultsss = str_replace('ETH-', '', $virtualcards->data->bnbdepositaddress); @endphp <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $bnbresultsss; ?>" alt="QR code" /><p>{{ __($virtualcards->data->bnbdepositaddress) }}</p></div></div></div></div>
    <div class="modal fade" id="solqrcode"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header d-block"><h5 class="modal-title">@lang('SOL DEPOSIT ADDRESS')</h5><p class="fs-12">@lang('Funds load to your card in 20minutes')</p></div><div class="modal-body text-center">@php $solresultsss = str_replace('ETH-', '', $virtualcards->data->soldepositaddress); @endphp <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $solresultsss; ?>" alt="QR code" /><p>{{ __($virtualcards->data->soldepositaddress) }}</p></div></div></div></div>
    <div class="modal fade" id="xrpqrcode"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header d-block"><h5 class="modal-title">@lang('XRP DEPOSIT ADDRESS')</h5><p class="fs-12">@lang('Funds load to your card in 20minutes')</p></div><div class="modal-body text-center">@php $xrpresultsss = str_replace('ETH-', '', $virtualcards->data->xrpdepositaddress); @endphp <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $xrpresultsss; ?>" alt="QR code" /><p>{{ __($virtualcards->data->xrpdepositaddress) }}</p></div></div></div></div>
    <div class="modal fade" id="paxgqrcode"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header d-block"><h5 class="modal-title">@lang('PAXG DEPOSIT ADDRESS')</h5><p class="fs-12">@lang('Funds load to your card in 20minutes')</p></div><div class="modal-body text-center">@php $paxgresultsss = str_replace('PAXG-', '', $virtualcards->data->paxgdepositaddress); @endphp <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $paxgresultsss; ?>" alt="QR code" /><p>{{ __($virtualcards->data->paxgdepositaddress) }}</p></div></div></div></div>

    <div class="modal fade" id="loadfunds" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content site-table-modal">
                <div class="modal-body popup-body">
                    <button type="button" class="modal-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                    <div class="popup-body-text">
                        <div class="title">{{ __('Load Funds') }}</div>
                        <p class="text-muted mb-3">{{ __('Load funds to your card') }}</p>

                        <form method="POST" action="{{ route('user.digitalloadfunds') }}">
                            @csrf
                            <input type="hidden" name="cardid" value="{{ $virtualcards->data->cardid }}">
                            <div class="form-group">
                                <label class="form-label">{{ __('Enter Amount') }} (USD)</label>
                                <input type="text" class="form-control" name="amount" id="amount" step="0.01" min="10" required>
                            </div>
                            <div class="action-btns">
                                <button type="submit" class="site-btn-sm primary-btn">{{ __('Submit') }}</button>
                                <button type="button" class="site-btn-sm red-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <p class="text-danger mb-0">$1 + {{ $general->bsiload_fee }}% {{ __('Load Fund Fees apply. Minimum $10 can be loaded. Funds take 24-48 hours to load.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addonCardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content site-table-modal">
                <div class="modal-body popup-body">
                    <button type="button" class="modal-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                    <div class="popup-body-text">
                        <div class="title">{{ __('Apply for addon card') }}</div>
                        <p class="text-muted mb-3">
                            {{ __('Add on card issuance fee of') }} <strong>${{ number_format((float) ($general->digifee ?? 0), 2) }}</strong>
                        </p>

                        <form method="POST" action="{{ route('user.digitaladdoncard') }}">
                            @csrf
                            <input type="hidden" name="cardid" value="{{ $virtualcards->data->cardid }}">
                            <div class="action-btns">
                                <button type="submit" class="site-btn-sm primary-btn">{{ __('Submit') }}</button>
                                <button type="button" class="site-btn-sm red-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
    <script>
        // 3DS countdown if timer exists
        (function () {
            const timerDisplay = document.getElementById('timer');
            if (!timerDisplay) return;
            let timeLeft = 30;
            const countdown = setInterval(() => {
                timeLeft--;
                timerDisplay.textContent = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    timerDisplay.textContent = '0';
                    location.reload();
                }
            }, 1000);
        })();

        // Poll 3DS status
        (function () {
            function checkStatus() {
                $.ajax({
                    url: '{{ route("user.checkstatus", $id) }}',
                    type: 'GET',
                    success: function (response) { $('#resultDiv').html(response); },
                    error: function (xhr) { $('#resultDiv').html('An error occurred: ' + xhr.status); }
                });
            }
            checkStatus();
            setInterval(checkStatus, 45000);
        })();

        // Poll OTP status
        (function () {
            function checkOTP() {
                $.ajax({
                    url: '{{ route("user.checkotp", $id) }}',
                    type: 'GET',
                    success: function (response) { $('#otpDiv').html(response); },
                    error: function (xhr) { $('#otpDiv').html('An error occurred: ' + xhr.status); }
                });
            }
            checkOTP();
            setInterval(checkOTP, 60000);
        })();
    </script>
    @endpush
@endsection

