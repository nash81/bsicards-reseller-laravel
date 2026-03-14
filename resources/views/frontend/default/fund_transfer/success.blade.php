@extends('frontend::layouts.user')
@section('title')
    {{ __('Fund Transfer') }}
@endsection

@section('style')
    <style>
        .transfer-status-wrap .status-card {
            border: 1px solid var(--bank-border-strong, rgba(15, 23, 42, .12));
            border-radius: 14px;
            overflow: hidden;
            background: var(--bank-surface, #fff);
        }
        .transfer-status-wrap .status-card.success {
            border-color: rgba(16, 185, 129, .35);
        }
        .transfer-status-wrap .status-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1rem 1.1rem;
            border-bottom: 1px solid var(--bank-border, rgba(15, 23, 42, .08));
            background: var(--bank-surface-soft, #f8fafc);
        }
        .transfer-status-wrap .status-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .28rem .72rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 600;
        }
        .transfer-status-wrap .status-pill.success {
            color: #0f766e;
            background: rgba(16, 185, 129, .16);
        }
        .transfer-status-wrap .status-body {
            padding: 1rem 1.1rem;
        }
        .transfer-status-wrap .status-title {
            font-weight: 700;
            margin-bottom: .35rem;
            color: #0f172a;
            font-size: 1.08rem;
        }
        .transfer-status-wrap .status-text {
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.55;
        }
        .transfer-status-wrap .summary-list {
            border: 1px solid var(--bank-border, rgba(15, 23, 42, .08));
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .transfer-status-wrap .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            padding: .78rem 1rem;
            border-bottom: 1px solid var(--bank-border, rgba(15, 23, 42, .08));
            font-size: .92rem;
        }
        .transfer-status-wrap .summary-item:last-child { border-bottom: 0; }
        .transfer-status-wrap .summary-label {
            color: #64748b;
            font-weight: 600;
        }
        .transfer-status-wrap .summary-value {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
            word-break: break-word;
        }
        .transfer-status-wrap .status-actions .site-btn {
            width: 100%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: .45rem;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-8 col-lg-10 col-md-12 col-12 mx-auto">
            <div class="transfer-status-wrap">
                <div class="status-card success">
                    <div class="status-head">
                        <span class="status-pill success">
                            <i data-lucide="check-circle-2"></i>
                            {{ __('Transfer Successful') }}
                        </span>
                        <i data-lucide="badge-check"></i>
                    </div>
                    <div class="status-body">
                        <div class="status-title">
                            {{ $responseData['currency'] }}{{ $responseData['amount'] }} {{ $message }}
                        </div>
                        <div class="status-text">
                            {{ __('The amount has been sent to') }} <strong>{{ $responseData['account'] }}</strong>.
                        </div>

                        <div class="summary-list">
                            <div class="summary-item">
                                <span class="summary-label">{{ __('Status') }}</span>
                                <span class="summary-value">{{ __('Completed') }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">{{ __('Amount') }}</span>
                                <span class="summary-value">{{ $responseData['currency'] }}{{ $responseData['amount'] }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">{{ __('Recipient Account') }}</span>
                                <span class="summary-value">{{ $responseData['account'] }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">{{ __('Transaction ID') }}</span>
                                <span class="summary-value">{{ $responseData['tnx'] }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">{{ __('Next Step') }}</span>
                                <span class="summary-value">{{ __('You can continue using your account') }}</span>
                            </div>
                        </div>

                        <div class="status-actions">
                            <a href="{{ route('user.fund_transfer.index') }}" class="site-btn polis-btn">
                                <i data-lucide="arrow-right"></i>{{ __('Transfer Again') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
