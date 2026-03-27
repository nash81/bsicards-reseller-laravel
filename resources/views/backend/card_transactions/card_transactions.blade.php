@extends('backend.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-title">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="title-content">
                        <h2 class="title">Card Transactions</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="site-table table-responsive">
                    <form action="{{ request()->url() }}" method="get" class="mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-5">
                                <label for="search" class="form-label">{{ __('Search') }}</label>
                                <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('Search in results...') }}">
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label for="perPage" class="form-label">{{ __('Per Page') }}</label>
                                <select name="perPage" id="perPage" class="form-select">
                                    @foreach([15, 30, 45, 60] as $perPage)
                                        <option value="{{ $perPage }}" @selected((int) request('perPage', 15) === $perPage)>{{ $perPage }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i data-lucide="search"></i>
                                    {{ __('Filter') }}
                                </button>
                                @if(request()->query())
                                    <a href="{{ request()->url() }}" class="btn btn-outline-secondary w-100">{{ __('Reset') }}</a>
                                @endif
                            </div>
                        </div>
                    </form>
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                @include('backend.filter.th', ['label' => 'Date', 'field' => 'created_at'])
                                @include('backend.filter.th', ['label' => 'Transaction Ref', 'field' => 'tx_ref'])
                                @include('backend.filter.th', ['label' => 'Details', 'field' => 'details'])
                                @include('backend.filter.th', ['label' => 'Amount', 'field' => 'amount'])
                                @include('backend.filter.th', ['label' => 'Balance', 'field' => 'balance'])
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($txn['created_at'])->format('d M Y, h:i A') }}</td>
                                    <td>{{ $txn['tx_ref'] }}</td>
                                    <td>{{ $txn['details'] }}</td>
                                    <td>
                                        <span class="badge {{ $txn['type'] == '-' ? 'bg-danger' : 'bg-success' }}">
                                            {{ $txn['type'] }}{{ number_format($txn['amount'], 2) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($txn['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $transactions->links('backend.include.__pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('single-script')
<script>
    (function ($) {
        'use strict';
        $('#perPage').on('change', function () {
            $(this).closest('form').trigger('submit');
        });
    })(jQuery);
</script>
@endpush
