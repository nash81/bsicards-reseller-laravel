@extends('frontend::layouts.user')
@section('title')
    {{ __('Virtual MasterCard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="site-card">
                <div class="site-card-header">
                    <h3 class="title-small">{{ __('Get New MasterCard') }}</h3>
                </div>
                <div class="site-card-body">
                    <form action="{{ route('user.mastervirtualnew') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Name on card') }}<span class="required">*</span></label>
                                    <input type="text" class="form-control" value="{{ $user->first_name }} {{ $user->last_name }}" readonly>
                                    <input type="hidden" name="nameoncard" value="{{ $user->first_name }} {{ $user->last_name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Email') }}<span class="required">*</span></label>
                                    <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                                    <input type="hidden" name="useremail" value="{{ $user->email }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Card Pin') }}<span class="required">*</span></label>
                                    <input type="text" class="form-control" maxlength="4" name="pin" pattern="\d{4}" placeholder="{{ __('Enter 4 digit pin') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i data-lucide="info"></i>
                            {{ __('Virtual MasterCard Issuance Fee') }}: <strong>${{ $general->bsiissue_fee }}</strong>. {{ __('Minimum balance $10.00 will be debited from your wallet.') }}
                        </div>

                        <div class="action-btns mt-3">
                            <button type="submit" class="site-btn primary-btn">
                                <i data-lucide="credit-card"></i> {{ __('Proceed') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @php
            $issuedCount = count($virtualcards->data ?? []);
            $pendingCount = count($pendingcards->data ?? []);
        @endphp

        <div class="col-xl-12">
            <div class="site-card virtual-card-status-tabs">
                <div class="site-card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h3 class="title-small mb-0">{{ __('Card Status') }}</h3>
                    <ul class="nav nav-tabs border-0" id="masterCardStatusTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="master-issued-tab" data-bs-toggle="tab" data-bs-target="#master-issued" type="button" role="tab" aria-controls="master-issued" aria-selected="true">
                                {{ __('Issued') }} <span class="count">{{ $issuedCount }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="master-pending-tab" data-bs-toggle="tab" data-bs-target="#master-pending" type="button" role="tab" aria-controls="master-pending" aria-selected="false">
                                {{ __('Pending') }} <span class="count">{{ $pendingCount }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="site-card-body p-0">
                    <div class="tab-content" id="masterCardStatusTabContent">
                        <div class="tab-pane fade show active" id="master-issued" role="tabpanel" aria-labelledby="master-issued-tab">
                            <div class="site-custom-table">
                                <div class="contents">
                                    <div class="site-table-list site-table-head">
                                        <div class="site-table-col">{{ __('Name on Card') }}</div>
                                        <div class="site-table-col">{{ __('Last 4 Digits') }}</div>
                                        <div class="site-table-col">{{ __('Action') }}</div>
                                    </div>
                                    @forelse ($virtualcards->data as $item)
                                        <div class="site-table-list">
                                            <div class="site-table-col"><div class="trx">{{ $item->nameoncard }}</div></div>
                                            <div class="site-table-col"><span class="site-badge badge-primary">**** {{ $item->lastfour ?? '' }}</span></div>
                                            <div class="site-table-col">
                                                <a href="{{ route('user.mastervirtualview', $item->cardid) }}" class="icon-btn">
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

                        <div class="tab-pane fade" id="master-pending" role="tabpanel" aria-labelledby="master-pending-tab">
                            <div class="site-custom-table">
                                <div class="contents">
                                    <div class="site-table-list site-table-head">
                                        <div class="site-table-col">{{ __('Name on Card') }}</div>
                                        <div class="site-table-col">{{ __('Status') }}</div>
                                    </div>
                                    @forelse ($pendingcards->data as $items)
                                        <div class="site-table-list">
                                            <div class="site-table-col"><div class="trx">{{ $items->nameoncard ?? '' }}</div></div>
                                            <div class="site-table-col"><span class="site-badge badge-pending">{{ ucfirst($items->status ?? '') }}</span></div>
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
