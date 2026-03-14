@php use App\Enums\TxnStatus; @endphp
@extends('frontend::layouts.user')
@section('title')
{{ __('Transactions') }}
@endsection
@push('style')
<link rel="stylesheet" href="{{ asset('front/css/daterangepicker.css') }}">
<style>
    #trxViewDetailsBox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 2000;
        display: none;
        background: rgba(0, 0, 0, 0.5);
        padding: clamp(12px, 3vw, 24px);
    }

    #trxViewDetailsBox.is-open {
        display: grid;
        place-items: center;
    }

    #trxViewDetailsBox .trx-modal-dialog {
        width: min(640px, 100%);
        max-height: calc(100vh - 24px);
        overflow: hidden;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.12);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3);
    }

    #trxViewDetailsBox .trx-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 20px 14px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }

    #trxViewDetailsBox .trx-title {
        margin: 0;
        font-size: 18px;
        line-height: 1.35;
        font-weight: 700;
        color: #101826;
    }

    #trxViewDetailsBox .trx-type {
        margin-top: 5px;
        font-size: 12px;
        color: #586579;
    }

    #trxViewDetailsBox .trx-modal-body {
        max-height: calc(100vh - 240px);
        overflow: auto;
        padding: 16px 20px 12px;
    }

    #trxViewDetailsBox .trx-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    @media (max-width: 640px) {
        #trxViewDetailsBox { padding: 12px; }
        #trxViewDetailsBox .trx-info-grid { grid-template-columns: 1fr; }
    }

    #trxViewDetailsBox .trx-item {
        padding: 10px 12px;
        border-radius: 10px;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.08);
    }

    #trxViewDetailsBox .trx-item.full {
        grid-column: 1 / -1;
    }

    #trxViewDetailsBox .trx-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #5d6a80;
        margin-bottom: 4px;
    }

    #trxViewDetailsBox .trx-value {
        font-size: 14px;
        color: #152238;
        word-break: break-word;
        line-height: 1.4;
    }

    #trxViewDetailsBox .trx-modal-footer {
        padding: 14px 20px 18px;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: flex-end;
    }

    body.trx-modal-open {
        overflow: hidden;
    }
</style>
@endpush
@section('content')
<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12 col-12">
        <div class="site-card">
            <div class="site-card-header">
                <div class="title-small">{{ __('All Transactions') }}</div>
            </div>
            <div class="site-card-body p-0 overflow-x-auto">
                <form>
                    <div class="table-filter">
                        <div class="filter">
                            <div class="single-f-box">
                                <label for="">{{ __('Transaction ID') }}</label>
                                <input class="search" type="text" name="trx" value="{{ request('trx') }}" autocomplete="off"/>
                            </div>
                            <div class="single-f-box">
                                <label for="">{{ __('Type') }}</label>
                                <select name="type" class="nice-select page-count w-100 ">
                                    <option value="all" @selected(request('type') == 'all')>{{ __('All Type') }}</option>
                                    <option value="deposit" @selected(request('type') == 'deposit')>{{ __('Deposit') }}</option>
                                    <option value="fund_transfer" @selected(request('type') == 'fund_transfer')>{{ __('Fund Transfer') }}</option>
                                    <option value="dps" @selected(request('type') == 'dps')>{{ __('DPS') }}</option>
                                    <option value="fdr" @selected(request('type') == 'fdr')>{{ __('FDR') }}</option>
                                    <option value="loan" @selected(request('type') == 'loan')>{{ __('Loan') }}</option>
                                    <option value="pay_bill" @selected(request('type') == 'pay_bill')>{{ __('Pay Bill') }}</option>
                                    <option value="withdraw" @selected(request('type') == 'withdraw')>{{ __('Withdraw') }}</option>
                                    <option value="referral" @selected(request('type') == 'referral')>{{ __('Referral') }}</option>
                                    <option value="portfolio" @selected(request('type') == 'portfolio')>{{ __('Portfolio') }}</option>
                                    <option value="rewards" @selected(request('type') == 'rewards')>{{ __('Rewards') }}</option>
                                </select>
                            </div>
                            <div class="single-f-box">
                                <label for="">{{ __('Date') }}</label>
                                <input type="text" name="daterange" value="{{ request('daterange') }}" autocomplete="off" />
                            </div>
                            <button class="apply-btn me-2" name="filter">
                                <i data-lucide="filter"></i>{{ __('Filter') }}
                            </button>
                            @if(request()->has('filter'))
                            <button type="button" class="apply-btn bg-danger reset-filter">
                                <i data-lucide="x"></i>{{ __('Reset Filter') }}
                            </button>
                            @endif
                        </div>
                        <div class="filter">
                            <div class="single-f-box w-auto ms-4 me-0">
                                <label for="">{{ __('Entries') }}</label>
                                <select name="limit" class="nice-select page-count" onchange="$('form').submit()">
                                    <option value="15" @selected(request('limit',15) == '15')>15</option>
                                    <option value="30" @selected(request('limit') == '30')>30</option>
                                    <option value="50" @selected(request('limit') == '50')>50</option>
                                    <option value="100" @selected(request('limit') == '100')>100</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="site-custom-table">
                    <div class="contents">
                        <div class="site-table-list site-table-head">
                            <div class="site-table-col">{{ __('Description') }}</div>
                            <div class="site-table-col">{{ __('Transactions ID') }}</div>
                            <div class="site-table-col">{{ __('Type') }}</div>
                            <div class="site-table-col">{{ __('Amount') }}</div>
                            <div class="site-table-col">{{ __('Charge') }}</div>
                            <div class="site-table-col">{{ __('Status') }}</div>
                            <div class="site-table-col">{{ __('Method') }}</div>
                            <div class="site-table-col">{{ __('View') }}</div>
                        </div>
                        @foreach ($transactions as $transaction)
                        <div class="site-table-list">
                            <div class="site-table-col">
                                <div class="description">
                                    <div class="event-icon">
                                        @if($transaction->type->value == 'deposit' || $transaction->type->value == 'manual_deposit')
                                        <i data-lucide="chevrons-down"></i>
                                        @elseif(Str::startsWith($transaction->type->value ,'dps'))
                                        <i data-lucide="archive"></i>
                                        @elseif(Str::startsWith($transaction->type->value ,'fdr'))
                                        <i data-lucide="book"></i>
                                        @elseif(Str::startsWith($transaction->type->value ,'loan'))
                                        <i data-lucide="alert-triangle"></i>
                                        @elseif($transaction->type->value == 'subtract')
                                        <i data-lucide="minus-circle"></i>
                                        @elseif($transaction->type->value == 'receive_money')
                                        <i data-lucide="arrow-down-left"></i>
                                        @elseif($transaction->type->value == 'reward_redeem')
                                        <i data-lucide="gift"></i>
                                        @else
                                        <i data-lucide="send"></i>
                                        @endif
                                    </div>
                                    <div class="content">
                                        <div class="title">
                                            {{ $transaction->description }}
                                            @if(!in_array($transaction->approval_cause,['none',""]))
                                            <span class="msg" data-bs-toggle="tooltip"
                                                data-bs-custom-class="custom-tooltip" data-bs-placement="top"
                                                data-bs-title="{{ $transaction->approval_cause }}"><i
                                                    data-lucide="message-square"></i>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="date">{{ $transaction->created_at }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="site-table-col">
                                <div class="trx fw-bold">{{ $transaction->tnx }}</div>
                            </div>
                            <div class="site-table-col">
                                <div class="type site-badge badge-primary">{{ ucfirst(str_replace('_',' ',$transaction->type->value)) }}</div>
                            </div>
                            <div class="site-table-col">
                                <div @class([
                                    "fw-bold",
                                    "green-color" => isPlusTransaction($transaction->type) == true,
                                    "red-color" => isPlusTransaction($transaction->type) == false
                                ])>{{ isPlusTransaction($transaction->type) == true ? '+' : '-' }}{{ $transaction->amount.' '.$currency }}</div>
                            </div>
                            <div class="site-table-col">
                                <div class="fw-bold red-color">-{{ $transaction->charge.' '.$currency }}</div>
                            </div>
                            <div class="site-table-col">
                                @if($transaction->status->value == 'failed')
                                    <div class="type site-badge badge-failed">{{ $transaction->status->value }}</div>
                                @elseif($transaction->status->value == 'success')
                                    <div class="type site-badge badge-success">{{ $transaction->status->value }}</div>
                                @elseif($transaction->status->value == 'pending')
                                    <div class="type site-badge badge-pending">{{ $transaction->status->value }}</div>
                                @endif
                            </div>
                            <div class="site-table-col">
                                <div class="fw-bold">{{ $transaction->method !== '' ? ucfirst(str_replace('-',' ',$transaction->method)) :  __('System') }}</div>
                            </div>
                            <div class="site-table-col">
                                <div class="action">
                                    <button type="button"
                                        class="icon-btn js-open-trx-modal"
                                        data-title="{{ $transaction->description }}"
                                        data-type="{{ $transaction->type->value }}"
                                        data-time="{{ $transaction->created_at }}"
                                        data-transaction-id="{{ $transaction->tnx }}"
                                        data-transaction='@json($transaction->manual_field_data ? json_decode($transaction->manual_field_data, true) : [])'
                                        data-message="{{ $transaction->action_message }}"
                                        data-amount="{{ isPlusTransaction($transaction->type) == true ? '+' : '-' }}{{ $transaction->amount.' '.$currency }}"
                                        data-charge="{{ $transaction->charge.' '.$currency  }}"
                                        data-status="{{ $transaction->status->value }}"
                                        data-method="{{ $transaction->method !== '' ? ucfirst(str_replace('-',' ',$transaction->method)) :  __('System') }}"
                                    >
                                        <i data-lucide="eye"></i>{{ __('Details') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if(count($transactions) == 0)
                    <div class="no-data-found">{{ __('No Data Found') }}</div>
                    @endif
                </div>

                <!-- Modal for Transaction View Details -->
                <div id="trxViewDetailsBox" class="modal" aria-hidden="true">
                    <div class="trx-modal-dialog" role="dialog" aria-modal="true" aria-label="{{ __('Transaction Details') }}">
                        <div class="trx-modal-header">
                            <div>
                                <h3 class="trx-title title-value"></h3>
                                <div class="trx-type type-value"></div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="status-value"></div>
                                <button type="button" class="modal-btn-close trx-modal-close" aria-label="Close">
                                    <i data-lucide="x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="trx-modal-body">
                            <div class="trx-info-grid">
                                <div class="trx-item">
                                    <div class="trx-label">{{ __('Time') }}</div>
                                    <div class="trx-value time-value"></div>
                                </div>
                                <div class="trx-item">
                                    <div class="trx-label">{{ __('Transaction ID') }}</div>
                                    <div class="trx-value trx-value"></div>
                                </div>
                                <div class="trx-item">
                                    <div class="trx-label">{{ __('Amount') }}</div>
                                    <div class="trx-value green-color amount-value"></div>
                                </div>
                                <div class="trx-item">
                                    <div class="trx-label">{{ __('Charge') }}</div>
                                    <div class="trx-value red-color charge-value"></div>
                                </div>
                                <div class="trx-item">
                                    <div class="trx-label">{{ __('Status') }}</div>
                                    <div class="trx-value status-text-value"></div>
                                </div>
                                <div class="trx-item">
                                    <div class="trx-label">{{ __('Method') }}</div>
                                    <div class="trx-value method-value"></div>
                                </div>
                                <div class="trx-item full message-wrapper d-none">
                                    <div class="trx-label">{{ __('Message') }}</div>
                                    <div class="trx-value message-value"></div>
                                </div>
                                <div class="custom-fields full"></div>
                            </div>
                        </div>
                        <div class="trx-modal-footer">
                            <button type="button" class="site-btn-sm polis-btn trx-modal-close" aria-label="Close">
                                <i data-lucide="check"></i>
                                {{ __('Close it') }}
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Modal for Transaction View Details end-->

                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
@push('js')
<script src="{{ asset('front/js/moment.min.js') }}"></script>
<script src="{{ asset('front/js/daterangepicker.min.js') }}"></script>
<script>

    // Initialize datepicker
    $('input[name="daterange"]').daterangepicker({
        opens: 'left'
    });

    @if(request('daterange') == null)
    // Set default is empty for date range
    $('input[name=daterange]').val('');
    @endif

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    const $trxModal = $('#trxViewDetailsBox');
    let trxModalBusy = false;

    // Ensure modal is not constrained by table/card wrappers with overflow/position rules.
    if (!$trxModal.parent().is('body')) {
        $('body').append($trxModal);
    }

    function renderTransactionModal(trigger) {
        var modal = $trxModal;
        var title = trigger.data('title') || '';
        var type = trigger.data('type') || '';
        var time = trigger.data('time') || '';
        var trx = trigger.data('transaction-id') || '';
        var amount = trigger.data('amount') || '';
        var charge = trigger.data('charge') || '';
        var method = trigger.data('method') || '';
        var status = trigger.data('status') || '';
        var message = trigger.data('message') || '';
        var transaction = trigger.data('transaction') || {};

        // Handle cases where jQuery returns JSON payload as string
        if (typeof transaction === 'string') {
            try {
                transaction = JSON.parse(transaction);
            } catch (e) {
                transaction = {};
            }
        }

        var statusElement;
        var additionalData = '';

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        if (transaction && typeof transaction === 'object') {
            $.each(transaction, function (key, value) {
                additionalData += '<div class="trx-item"><div class="trx-label">'
                    + escapeHtml(capitalizeFirstLetter(String(key).replaceAll('_', ' ')))
                    + '</div><div class="trx-value">'
                    + escapeHtml(value)
                    + '</div></div>';
            });
        }

        if (status === 'failed') {
            statusElement = `<div class="type site-badge badge-failed">{{ __('Failed') }}</div>`;
        } else if (status === 'success') {
            statusElement = `<div class="type site-badge badge-success">{{ __('Success') }}</div>`;
        } else {
            statusElement = `<div class="type site-badge badge-pending">{{ __('Pending') }}</div>`;
        }

        modal.find('.title-value').text(title);
        modal.find('.type-value').text(type ? capitalizeFirstLetter(String(type).replaceAll('_', ' ')) : '');
        modal.find('.trx-value').text(trx);
        modal.find('.time-value').text(time);
        modal.find('.amount-value').text(amount);
        modal.find('.charge-value').text(charge);
        modal.find('.method-value').text(method);
        modal.find('.status-value').html(statusElement);
        modal.find('.status-text-value').text(status ? capitalizeFirstLetter(String(status)) : '');
        modal.find('.custom-fields').html(additionalData);
        modal.find('.message-value').text(message);
        modal.find('.message-wrapper').toggleClass('d-none', !message);
    }

    function openTrxModal() {
        $trxModal.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('trx-modal-open');
        trxModalBusy = false;
    }

    function closeTrxModal() {
        $trxModal.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('trx-modal-open');

        $trxModal.find('.title-value, .type-value, .trx-value, .time-value, .amount-value, .charge-value, .method-value, .message-value').text('');
        $trxModal.find('.status-value, .custom-fields, .message-value, .status-text-value').empty();
        $trxModal.find('.message-wrapper').addClass('d-none');
        trxModalBusy = false;
    }

    // Open modal only after content is rendered to avoid visual flash.
    $(document).off('click.trx', '.js-open-trx-modal').on('click.trx', '.js-open-trx-modal', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (trxModalBusy || $trxModal.hasClass('is-open')) {
            return;
        }

        trxModalBusy = true;
        var trigger = $(this);
        renderTransactionModal(trigger);
        requestAnimationFrame(openTrxModal);
    });

    $(document).off('click.trx.close', '.trx-modal-close').on('click.trx.close', '.trx-modal-close', function (e) {
        e.preventDefault();
        closeTrxModal();
    });

    // Click outside dialog closes modal.
    $trxModal.off('click.trx.overlay').on('click.trx.overlay', function (e) {
        if (e.target === this) {
            closeTrxModal();
        }
    });

    // ESC support.
    $(document).off('keydown.trx.modal').on('keydown.trx.modal', function (e) {
        if (e.key === 'Escape' && $trxModal.hasClass('is-open')) {
            closeTrxModal();
        }
    });

    // Reset filter
    $('.reset-filter').on('click',function(){
        window.location.href = "{{ route('user.transactions') }}";
    });

</script>
@endpush
@endsection
