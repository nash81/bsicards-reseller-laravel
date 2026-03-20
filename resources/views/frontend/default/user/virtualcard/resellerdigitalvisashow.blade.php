@extends('frontend::layouts.user')
@section('title')
    {{ __('Digital Visa Wallet Card Details') }}
@endsection
@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="site-card">
            <div class="site-card-header">
                <h3 class="title-small">{{ __('Card Details') }}</h3>
            </div>
            <div class="site-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Name on Card') }}</label>
                            <div>{{ $card->data->nameoncard ?? '' }}</div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Last 4 Digits') }}</label>
                            <div>**** {{ $card->data->lastfour ?? '' }}</div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Status') }}</label>
                            <div><span class="site-badge badge-primary">{{ ucfirst($card->data->status ?? '') }}</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <form id="fundCardForm">
                            @csrf
                            <input type="hidden" name="cardid" value="{{ $card->data->cardid }}">
                            <div class="form-group">
                                <label>{{ __('Amount') }}</label>
                                <input type="number" class="form-control" name="amount" min="5" required>
                            </div>
                            <button type="submit" class="site-btn primary-btn mt-2">{{ __('Fund Card') }}</button>
                        </form>
                        <div class="mt-3">
                            <button class="site-btn-sm red-btn" id="blockCardBtn">{{ __('Block') }}</button>
                            <button class="site-btn-sm primary-btn" id="unblockCardBtn">{{ __('Unblock') }}</button>
                            <button class="site-btn-sm info-btn" id="getOtpBtn">{{ __('Get OTP') }}</button>
                        </div>
                        <div id="otpResult" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="/js/reseller/visaWalletCards.js"></script>
<script>
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
    let cardid = '{{ $card->data->cardid }}';
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
    let cardid = '{{ $card->data->cardid }}';
    fetch('/user/reseller-digital-visa-unblock', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cardid })
    }).then(() => location.reload());
});
// Get OTP
$('#getOtpBtn').on('click', function() {
    let cardid = '{{ $card->data->cardid }}';
    fetch('/user/reseller-digital-visa-otp/' + cardid)
        .then(response => response.json())
        .then(data => {
            $('#otpResult').html('<div class="alert alert-info">OTP: ' + (data.otp || 'N/A') + '</div>');
        });
});
</script>
@endsection

