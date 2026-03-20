@extends('frontend::layouts.user')
@section('title')
    {{ __('Digital Visa Wallet Cards') }}
@endsection
@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="site-card">
            <div class="site-card-header">
                <h3 class="title-small">{{ __('Get New Digital Visa Wallet Card') }}</h3>
            </div>
            <div class="site-card-body">
                <form id="createCardForm" action="{{ route('user.reseller.digital.visa.create') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('First Name') }}<span class="required">*</span></label>
                                <input type="text" class="form-control" name="firstname" value="{{ $user->first_name }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Last Name') }}<span class="required">*</span></label>
                                <input type="text" class="form-control" name="lastname" value="{{ $user->last_name }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Email') }}<span class="required">*</span></label>
                                <input type="email" class="form-control" name="useremail" value="{{ $user->email }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i data-lucide="info"></i>
                        {{ __('Digital Visa Wallet Card Issuance Fee') }}: <strong>${{ $general->bsiissue_fee }}</strong>. {{ __('Minimum balance $5.00 Plus fees will be debited from your wallet. Fee: ') }}<strong>${{ $general->bsifixed_fee }}</strong> + <strong>{{ $general->bsiload_fee }}%</strong>
                    </div>
                    <div class="action-btns mt-3">
                        <button type="submit" class="site-btn primary-btn" id="proceedBtn">
                            <span id="proceedBtnText"><i data-lucide="credit-card"></i> {{ __('Proceed') }}</span>
                            <span id="proceedBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="site-card virtual-card-status-tabs">
            <div class="site-card-header d-flex flex-wrap align-items-center justify-content-between">
                <h3 class="title-small mb-0">{{ __('Card Status') }}</h3>
                <ul class="nav nav-tabs border-0" id="visaCardStatusTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="visa-issued-tab" data-bs-toggle="tab" data-bs-target="#visa-issued" type="button" role="tab" aria-controls="visa-issued" aria-selected="true">
                            {{ __('Issued') }} <span class="count">{{ count($cards->data ?? []) }}</span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="site-card-body p-0">
                <div class="tab-content" id="visaCardStatusTabContent">
                    <div class="tab-pane fade show active" id="visa-issued" role="tabpanel" aria-labelledby="visa-issued-tab">
                        <div class="site-custom-table">
                            <div class="contents">
                                <div class="site-table-list site-table-head">
                                    <div class="site-table-col">{{ __('Name on Card') }}</div>
                                    <div class="site-table-col">{{ __('Last 4 Digits') }}</div>
                                    <div class="site-table-col">{{ __('Action') }}</div>
                                </div>
                                @forelse ($cards->data as $item)
                                    <div class="site-table-list">
                                        <div class="site-table-col"><div class="trx">{{ $item->nameoncard }}</div></div>
                                        <div class="site-table-col"><span class="site-badge badge-primary">**** {{ $item->lastfour ?? '' }}</span></div>
                                        <div class="site-table-col">
                                            <a href="{{ route('user.reseller.digital.visa.card', $item->cardid) }}" class="icon-btn">
                                                <i data-lucide="eye"></i>{{ __('View') }}
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="no-data-found">{{ __('No Cards Found') }}</div>
                                @endforelse
                            </div>
                        </div>
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
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createCardForm');
    const proceedBtn = document.getElementById('proceedBtn');
    const proceedBtnText = document.getElementById('proceedBtnText');
    const proceedBtnSpinner = document.getElementById('proceedBtnSpinner');
    form.addEventListener('submit', function() {
        proceedBtn.disabled = true;
        proceedBtnText.classList.add('d-none');
        proceedBtnSpinner.classList.remove('d-none');
    });
});
</script>
@endsection
