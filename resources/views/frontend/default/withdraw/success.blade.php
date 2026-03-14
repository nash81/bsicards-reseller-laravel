@extends('frontend::layouts.user')
@section('title')
    {{ __('Withdraw Successful') }}
@endsection

@section('style')
    <style>
        .withdraw-status-wrap .status-card {
            border: 1px solid var(--bank-border-strong, rgba(15, 23, 42, .12));
            border-radius: 14px;
            overflow: hidden;
            background: var(--bank-surface, #fff);
        }
        .withdraw-status-wrap .status-card.pending {
            border-color: rgba(245, 158, 11, .4);
        }
        .withdraw-status-wrap .status-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1rem 1.1rem;
            border-bottom: 1px solid var(--bank-border, rgba(15, 23, 42, .08));
            background: var(--bank-surface-soft, #f8fafc);
        }
        .withdraw-status-wrap .status-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .28rem .72rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 600;
        }
        .withdraw-status-wrap .status-pill.success {
            color: #0f766e;
            background: rgba(16, 185, 129, .16);
        }
        .withdraw-status-wrap .status-pill.pending {
            color: #b45309;
            background: rgba(245, 158, 11, .16);
        }
        .withdraw-status-wrap .status-body {
            padding: 1rem 1.1rem;
        }
        .withdraw-status-wrap .status-title {
            font-weight: 700;
            margin-bottom: .35rem;
            color: #0f172a;
            font-size: 1.08rem;
        }
        .withdraw-status-wrap .status-text {
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.55;
        }
        .withdraw-status-wrap .summary-list {
            border: 1px solid var(--bank-border, rgba(15, 23, 42, .08));
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .withdraw-status-wrap .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            padding: .78rem 1rem;
            border-bottom: 1px solid var(--bank-border, rgba(15, 23, 42, .08));
            font-size: .92rem;
        }
        .withdraw-status-wrap .summary-item:last-child { border-bottom: 0; }
        .withdraw-status-wrap .summary-label {
            color: #64748b;
            font-weight: 600;
        }
        .withdraw-status-wrap .summary-value {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
            word-break: break-word;
        }
        .withdraw-status-wrap .status-actions .site-btn {
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
        $isPending = true;
        preg_match('/([^:]+)\s*:\s*(.+)/', (string) ($notify['strong'] ?? ''), $txnParts);
        $txnLabel = $txnParts[1] ?? __('Transaction ID');
        $txnValue = $txnParts[2] ?? ($notify['strong'] ?? '-');
    @endphp
    <div class="row">
        <div class="col-xl-8 col-lg-10 col-md-12 col-12 mx-auto">
            <div class="withdraw-status-wrap">
                <div class="status-card pending">
                    <div class="status-head">
                        <span class="status-pill pending">
                            <i data-lucide="clock-3"></i>
                            {{ __('Pending Review') }}
                        </span>
                        <i data-lucide="hourglass"></i>
                    </div>
                    <div class="status-body">
                        <div class="status-title">{{ $notify['title'] ?? __('Withdraw Request Submitted') }}</div>
                        <div class="status-text">{{ $notify['p'] ?? __('Your withdraw request has been submitted and is awaiting admin approval.') }}</div>

                        <div class="summary-list">
                            <div class="summary-item">
                                <span class="summary-label">{{ __('Status') }}</span>
                                <span class="summary-value">{{ __('Pending Approval') }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">{{ __($txnLabel) }}</span>
                                <span class="summary-value">{{ $txnValue }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">{{ __('Next Step') }}</span>
                                <span class="summary-value">{{ __('Wait for admin approval') }}</span>
                            </div>
                        </div>

                        <div class="status-actions">
                            <a href="{{ $notify['action'] ?? route('user.withdraw.view') }}" class="site-btn polis-btn">
                                <i data-lucide="arrow-right"></i>{{ __($notify['a'] ?? 'Withdraw Again') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
