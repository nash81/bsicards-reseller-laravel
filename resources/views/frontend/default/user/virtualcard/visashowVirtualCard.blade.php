@extends('frontend::layouts.user')
@section('title')
    {{ __('Virtual VisaCard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="site-card">
                <div class="site-card-header">
                    <h3 class="title-small">{{ __('Virtual VisaCard') }}</h3>
                </div>
                <div class="site-card-body">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-4 col-xl-4">
                            <div class="virtual-bank-card visa-theme">
                                <div class="card-top">
                                    <span class="chip"></span>
                                    <span class="brand">VISA</span>
                                </div>
                                <div class="card-number">{{ preg_replace('/(\d{4})(?=\d)/', '$1 ', $virtualcards->data->card_number ?? '') }}</div>
                                <div class="card-bottom">
                                    <div>
                                        <div class="label">{{ __('Card Holder') }}</div>
                                        <div class="value">{{ $user->first_name }} {{ $user->last_name }}</div>
                                    </div>
                                    <div>
                                        <div class="label">{{ __('Expires') }}</div>
                                        <div class="value">{{ $virtualcards->data->expiry_month ?? '--' }}</div>
                                    </div>
                                    <div>
                                        <div class="label">CVV</div>
                                        <div class="value">{{ $virtualcards->data->cvv ?? '***' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 col-xl-8">
                            <div class="card-info-grid">
                                <div class="info-block">
                                    <div class="k">{{ __('Balance (USD)') }}</div>
                                    <div class="v">${{ number_format(($virtualcards->data->available_balance ?? 0) / 100, 2) }}</div>
                                </div>
                                <div class="info-block full">
                                    <div class="k">{{ __('Billing Address') }}</div>
                                    <div class="v small">
                                        {{ $virtualcards->data->billing_address->billing_address1 ?? '' }},
                                        {{ $virtualcards->data->billing_address->billing_city ?? '' }},
                                        {{ $virtualcards->data->billing_address->state ?? '' }},
                                        {{ $virtualcards->data->billing_address->billing_country ?? '' }},
                                        {{ $virtualcards->data->billing_address->billing_zip_code ?? '' }}
                                    </div>
                                </div>
                                <div class="info-block">
                                    <div class="k">{{ __('Status') }}</div>
                                    <div class="v">
                                        @if(($virtualcards->data->is_active ?? '') == 'active')
                                            <span class="site-badge badge-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="site-badge badge-failed">{{ __('Blocked') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="info-block">
                                    <div class="k">{{ __('Network') }}</div>
                                    <div class="v">{{ ucfirst($virtualcards->data->brand ?? 'Visa') }}</div>
                                </div>
                                <div class="info-actions full">
                                    <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#loadfunds">
                                        <i data-lucide="wallet"></i>{{ __('Load Funds') }}
                                    </button>
                                    @if(($virtualcards->data->is_active ?? '') != 'active')
                                        <a href="{{ route('user.visavirtualunblock', $virtualcards->data->cardid) }}" class="site-btn-sm primary-btn">{{ __('Unblock') }}</a>
                                    @else
                                        <a href="{{ route('user.visavirtualblock', $virtualcards->data->cardid) }}" class="site-btn-sm red-btn" onclick="return confirm('{{ __('Are you sure you want to block the card?') }}')">{{ __('Block') }}</a>
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
                <div class="site-card-body p-0">
                    <div class="site-custom-table">
                        <div class="contents">
                            <div class="site-table-list site-table-head">
                                <div class="site-table-col">{{ __('Type') }}</div>
                                <div class="site-table-col">{{ __('Date') }}</div>
                                <div class="site-table-col">{{ __('Description') }}</div>
                                <div class="site-table-col">{{ __('Amount') }}</div>
                            </div>
                            @if(isset($decodedTransactions->data))
                                @foreach($decodedTransactions->data->response->data->cardTransactions as $transaction)
                                    @if($transaction->status == 'success')
                                        <div class="site-table-list">
                                            <div class="site-table-col"><div class="trx">{{ ucfirst($transaction->type) }}</div></div>
                                            <div class="site-table-col">
                                                <div class="trx">{{ \Carbon\Carbon::parse($transaction->createdAt)->format('F d, Y H:i') }}<br>{{ ucfirst($transaction->status) }}</div>
                                            </div>
                                            <div class="site-table-col"><div class="trx">{{ $transaction->narrative }}</div></div>
                                            <div class="site-table-col">
                                                @if($transaction->type == 'credit')
                                                    <span class="green-color fw-bold">USD {{ number_format($transaction->centAmount / 100, 2) }}</span>
                                                @else
                                                    <span class="red-color fw-bold">USD {{ number_format($transaction->centAmount / 100, 2) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="no-data-found">{{ __('No transactions found') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

                        <form method="POST" action="{{ route('user.visavirtualloadfunds') }}">
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
                    <p class="text-danger mb-0">{{ $general->bsiload_fee }}% {{ __('Load Fund Fees apply. Minimum $10 can be loaded') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
