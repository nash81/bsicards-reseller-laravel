@extends('frontend::layouts.user')
@section('title')
{{ __('Deposit Success') }}
@endsection

@section('style')
    <style>
        .deposit-status-wrap .status-card {
            border: 1px solid var(--bank-border-strong, rgba(15, 23, 42, .12));
            border-radius: 14px;
            overflow: hidden;
            background: var(--bank-surface, #fff);
        }
        .deposit-status-wrap .status-card.pending {
            border-color: rgba(245, 158, 11, .4);
        }
        .deposit-status-wrap .status-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1rem 1.1rem;
            border-bottom: 1px solid var(--bank-border, rgba(15, 23, 42, .08));
            background: var(--bank-surface-soft, #f8fafc);
        }
        .deposit-status-wrap .status-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .28rem .72rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 600;
        }
        .deposit-status-wrap .status-pill.success {
            color: #0f766e;
            background: rgba(16, 185, 129, .16);
        }
        .deposit-status-wrap .status-pill.pending {
            color: #b45309;
            background: rgba(245, 158, 11, .16);
        }
        .deposit-status-wrap .status-body {
            padding: 1rem 1.1rem;
        }
        .deposit-status-wrap .status-title {
            font-weight: 700;
            margin-bottom: .35rem;
            color: #0f172a;
            font-size: 1.08rem;
        }
        .deposit-status-wrap .status-text {
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.55;
        }
        .deposit-status-wrap .summary-list {
            border: 1px solid var(--bank-border, rgba(15, 23, 42, .08));
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .deposit-status-wrap .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            padding: .78rem 1rem;
            border-bottom: 1px solid var(--bank-border, rgba(15, 23, 42, .08));
            font-size: .92rem;
        }
        .deposit-status-wrap .summary-item:last-child { border-bottom: 0; }
        .deposit-status-wrap .summary-label {
            color: #64748b;
            font-weight: 600;
        }
        .deposit-status-wrap .summary-value {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
            word-break: break-word;
        }
        .deposit-status-wrap .status-actions .site-btn {
            width: 100%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: .45rem;
        }
    </style>
@endsection

@section('content')
    @php
        $isPending = (($notify['status'] ?? '') === 'pending') || str_contains(strtolower($notify['card-header'] ?? ''), 'pending');
        preg_match('/([^:]+)\s*:\s*(.+)/', (string) ($notify['strong'] ?? ''), $txnParts);
        $txnLabel = $txnParts[1] ?? __('Transaction ID');
        $txnValue = $txnParts[2] ?? ($notify['strong'] ?? '-');
    @endphp
    <div class="row">
        <div class="col-xl-8 col-lg-10 col-md-12 col-12 mx-auto">
            <div class="deposit-status-wrap">
                <div class="status-card {{ $isPending ? 'pending' : 'success' }}">
                    <div class="status-head">
                        <span class="status-pill {{ $isPending ? 'pending' : 'success' }}">
                            <i data-lucide="{{ $isPending ? 'clock-3' : 'check-circle-2' }}"></i>
                            {{ $isPending ? __('Pending Review') : __('Successful') }}
                        </span>
                        <i data-lucide="{{ $isPending ? 'hourglass' : 'badge-check' }}"></i>
                    </div>
                    <div class="status-body">
                        <div class="status-title">{{ $notify['title'] ?? __('Deposit update') }}</div>
                        <div class="status-text">{{ $notify['p'] ?? __('Your deposit status has been updated.') }}</div>

                        <div class="summary-list">
                            <div class="summary-item">
                                <span class="summary-label">{{ __('Status') }}</span>
                                <span class="summary-value">{{ $isPending ? __('Pending') : __('Completed') }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">{{ __($txnLabel) }}</span>
                                <span class="summary-value">{{ $txnValue }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">{{ __('Next Step') }}</span>
                                <span class="summary-value">{{ $isPending ? __('Wait for admin approval') : __('You can continue using your account') }}</span>
                            </div>
                        </div>

                        <div class="status-actions">
                            <a href="{{ $notify['action'] ?? route('user.deposit.amount') }}" class="site-btn polis-btn">
                                <i data-lucide="arrow-right"></i>{{ __($notify['a'] ?? 'Deposit Again') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

