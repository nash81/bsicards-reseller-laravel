@extends('frontend::layouts.user')
@section('title')
    {{ __('Virtual VisaCard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="site-card">
                <div class="site-card-header">
                    <h3 class="title-small">{{ __('Get New VisaCard') }}</h3>
                </div>
                <div class="site-card-body">
                    <form action="{{ route('user.visavirtualnew') }}" method="POST" enctype="multipart/form-data">
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
                                    <label class="form-label">{{ __('User Selfie') }}<span class="required">*</span></label>
                                    <input type="file" class="form-control" name="userphoto" id="userphoto" accept="image/*" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('National ID / Passport Number') }}<span class="required">*</span></label>
                                    <input type="text" class="form-control" name="nationalidnumber" id="nationalidnumber" placeholder="{{ __('Enter Passport/National ID Number') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Date Of Birth') }}<span class="required">*</span></label>
                                    <input type="date" class="form-control" name="dob" id="dob" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('National ID / Passport Document') }}<span class="required">*</span></label>
                                    <input type="file" class="form-control" name="nationalid" id="nationalid" accept="image/*" required>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i data-lucide="info"></i>
                            {{ __('Virtual VisaCard Issuance Fee') }}: <strong>${{ $general->bsiissue_fee }}</strong>. {{ __('Minimum balance $10.00 will be debited from your wallet.') }}
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
                    <ul class="nav nav-tabs border-0" id="visaCardStatusTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="visa-issued-tab" data-bs-toggle="tab" data-bs-target="#visa-issued" type="button" role="tab" aria-controls="visa-issued" aria-selected="true">
                                {{ __('Issued') }} <span class="count">{{ $issuedCount }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="visa-pending-tab" data-bs-toggle="tab" data-bs-target="#visa-pending" type="button" role="tab" aria-controls="visa-pending" aria-selected="false">
                                {{ __('Pending') }} <span class="count">{{ $pendingCount }}</span>
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
                                    @forelse ($virtualcards->data as $item)
                                        <div class="site-table-list">
                                            <div class="site-table-col"><div class="trx">{{ $item->nameoncard }}</div></div>
                                            <div class="site-table-col"><span class="site-badge badge-primary">**** {{ $item->lastfour ?? '' }}</span></div>
                                            <div class="site-table-col">
                                                <a href="{{ route('user.visavirtualview', $item->cardid) }}" class="icon-btn">
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

                        <div class="tab-pane fade" id="visa-pending" role="tabpanel" aria-labelledby="visa-pending-tab">
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
