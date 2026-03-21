@extends('frontend::layouts.user')
@section('title')
    {{ __('Digital Visa Wallet Card Details') }}
@endsection
@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="site-card">
            <div class="site-card-header">
                <h3 class="title-small">{{ __('Visa Digital Card') }}</h3>
            </div>
            <div class="site-card-body">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-4 col-xl-4">
                        <div class="virtual-bank-card digital-theme visa-theme">
                            <div class="card-top">
                                <span class="chip"></span>
                                <span class="brand">VISA DIGITAL</span>
                            </div>
                            <div class="card-number">
                                {{ isset($card->data->provider->getcardpan->card_number) ? preg_replace('/(\d{4})(?=\d)/', '$1 ', $card->data->provider->getcardpan->card_number) : '**** **** **** ' . ($card->data->local->lastfour ?? '') }}
                            </div>
                            <div class="card-bottom">
                                <div>
                                    <div class="label">{{ __('Card Holder') }}</div>
                                    <div class="value">{{ isset($card->data->local->nameoncard) ? strtoupper($card->data->local->nameoncard) : '' }}</div>
                                </div>
                                <div>
                                    <div class="label">{{ __('Expires') }}</div>
                                    <div class="value">{{ $card->data->provider->getcardpan->expiry_date ?? ($card->data->provider->getcard->expiresAt ?? ($card->data->local->month ?? '').'/'.($card->data->local->year ?? '')) }}</div>
                                </div>
                                <div>
                                    <div class="label">CVV</div>
                                    <div class="value">{{ $card->data->provider->getcardpan->cvv ?? '***' }}</div>
                                </div>
                            </div>
{{--                            <div class="visa-logo" style="position:absolute;bottom:10px;right:20px;width:60px;">--}}
{{--                                <img src="/images/visa-logo.svg" alt="Visa" style="width:100%;" />--}}
{{--                            </div>--}}
                        </div>
                    </div>
                    <div class="col-lg-8 col-xl-8">
                        <div class="card-info-grid">
                            <div class="info-block">
                                <div class="k">{{ __('Balance (USD)') }}</div>
                                <div class="v">{{ $card->data->provider->getcard->balance ?? '0.00' }}</div>
                            </div>
                            <div class="info-block">
                                <div class="k">{{ __('Status') }}</div>
                                <div class="v">
                                    @if(($card->data->provider->getcard->status ?? '') == 'ACTIVE')
                                        <span class="site-badge badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="site-badge badge-failed">{{ __('Blocked') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="info-block">
                                <div class="k">{{ __('Network') }}</div>
                                <div class="v">{{ ucfirst($card->data->local->brand ?? $card->data->provider->getcard->paymentSystem ?? '') }} {{ ucfirst($card->data->local->type ?? '') }}</div>
                            </div>
                            <div class="info-block" id="otpBlock">
                                <div class="k">{{ __('OTP') }}</div>
                                <div class="v" id="otpValue">{{ __('Click Get OTP to retrieve code') }}</div>
                            </div>
                            <div class="info-block full">
                                <div class="k">{{ __('Billing Address') }}</div>
                                <div class="v small">
                                    {{ $card->data->local->address1 ?? '' }}, {{ $card->data->local->city ?? '' }}, {{ $card->data->local->state ?? '' }},
                                    {{ $card->data->local->country ?? '' }} {{ $card->data->local->postalCode ?? '' }}
                                </div>
                            </div>
                            <div class="info-actions full mt-3">
                                @if(($card->data->provider->getcard->status ?? '') == 'ACTIVE')
                                    <button class="site-btn-sm red-btn" id="blockCardBtn">{{ __('Block') }}</button>
                                @else
                                    <button class="site-btn-sm primary-btn" id="unblockCardBtn">{{ __('Unblock') }}</button>
                                @endif
                                <button class="site-btn-sm info-btn" id="getOtpBtn">{{ __('Get OTP') }}</button>
                                <button class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#fundCardModal">{{ __('Fund Card') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="site-card mt-4">
            <div class="site-card-header">
                <h3 class="title-small">{{ __('Card Transaction History') }}</h3>
            </div>
            <div class="site-card-body">
                <ul class="nav nav-tabs border-0" id="transactionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="existing-tab" data-bs-toggle="tab" data-bs-target="#existing" type="button" role="tab" aria-controls="existing" aria-selected="true">{{ __('Transactions') }}</button>
                    </li>
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
                                        <th>{{ __('Currency') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if(isset($card->data->provider->getcardtransactions->data) && count($card->data->provider->getcardtransactions->data) > 0)
                                    @foreach($card->data->provider->getcardtransactions->data as $txn)
                                        <tr>
                                            <td>
                                                @if(isset($txn->date) && \Illuminate\Support\Carbon::hasFormat($txn->date, 'Y-m-d H:i:s'))
                                                    {{ \Carbon\Carbon::parse($txn->date)->format('d M Y, H:i') }}
                                                @elseif(isset($txn->date))
                                                    {{ \Carbon\Carbon::parse($txn->date)->format('d M Y, H:i') }}
                                                @else
                                                    {{ '' }}
                                                @endif
                                            </td>
                                            <td>{{ $txn->type ?? '' }}</td>
                                            <td>{{ $txn->description ?? '' }}</td>
                                            <td>{{ $txn->amount ?? '' }}</td>
                                            <td>{{ $txn->currencyCode ?? '' }}</td>
                                            <td>{{ $txn->status ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="6" class="text-center">{{ __('No transactions found.') }}</td></tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fund Card Modal -->
<div class="modal fade" id="fundCardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content site-table-modal">
            <div class="modal-body popup-body">
                <button type="button" class="modal-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i data-lucide="x"></i>
                </button>
                <div class="popup-body-text">
                    <div class="title">{{ __('Fund Card') }}</div>
                    <form id="fundCardForm">
                        @csrf
                        <input type="hidden" name="cardid" value="{{ $card->data->local->cardid ?? '' }}">
                        <div class="form-group">
                            <label>{{ __('Amount') }}</label>
                            <input type="number" class="form-control" name="amount" min="5" required>
                        </div>
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
@endsection
@section('script')
<script>
(function() {
    // Check if jQuery is loaded
    if (typeof window.jQuery === 'undefined') {
        var err = document.createElement('div');
        err.style.color = 'red';
        err.style.fontWeight = 'bold';
        err.innerText = 'Error: jQuery is not loaded. Please ensure jQuery is included before this script.';
        document.body.prepend(err);
        return;
    }
    $(function() {
        // Fund card
        $('#fundCardForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch('/user/reseller-digital-visa-fund', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: formData
            }).then(() => location.reload());
        });
        // Block card
        $('#blockCardBtn').on('click', function() {
            let cardid = '{{ $card->data->local->cardid }}';
            fetch('/user/reseller-digital-visa-block', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ cardid })
            }).then(() => location.reload());
        });
        // Unblock card
        $('#unblockCardBtn').on('click', function() {
            let cardid = '{{ $card->data->local->cardid }}';
            fetch('/user/reseller-digital-visa-unblock', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ cardid })
            }).then(() => location.reload());
        });
        // Delegated event binding for Get OTP (handles dynamic DOM)
        $(document).on('click', '#getOtpBtn', function() {
            console.log('Get OTP button clicked');
            var $btn = $(this);
            $btn.prop('disabled', true);
            var originalText = $btn.html();
            $btn.html('<span class="spinner-border spinner-border-sm"></span> {{ __('Loading...') }}');
            let cardid = '{{ $card->data->local->cardid }}';
            fetch('/user/reseller-digital-visa-otp/' + cardid)
                .then(function(response) {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(function(data) {
                    $btn.prop('disabled', false);
                    $btn.html(originalText);
                    if (data.otp) {
                        $('#otpValue').html('<span class="site-badge badge-success">' + data.otp + '</span>');
                    } else {
                        $('#otpValue').html('<span class="site-badge badge-failed">{{ __('No OTP received') }}</span>');
                    }
                    console.log('OTP response:', data);
                })
                .catch(function(error) {
                    $btn.prop('disabled', false);
                    $btn.html(originalText);
                    $('#otpValue').html('<span class="site-badge badge-failed">{{ __('No OTP received') }}</span>');
                    console.error('OTP fetch error:', error);
                });
        });
    });
})();
</script>
@endsection

