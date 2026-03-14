@extends('backend.deposit.index')
@section('title')
    {{ __('Pending Manual Deposit') }}
@endsection

@section('style')
    <style>
        .manual-deposit-modal .popup-body-text {
            max-height: 78vh;
            overflow-y: auto;
        }
        .manual-deposit-modal .proof-preview-wrap {
            margin-top: .55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 140px;
            height: 95px;
            border: 1px solid rgba(15, 23, 42, .12);
            border-radius: 10px;
            background: #fff;
            overflow: hidden;
        }
        .manual-deposit-modal .proof-preview-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .manual-deposit-modal .proof-file-link {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-top: .45rem;
            font-size: .8rem;
            font-weight: 600;
        }
        .manual-deposit-modal .deposit-meta-list .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
        }
        .manual-deposit-modal .manual-data-list .list-group-item {
            display: block;
        }
        .manual-deposit-modal .manual-key {
            display: block;
            margin-bottom: .4rem;
            font-weight: 600;
        }
    </style>
@endsection

@section('deposit_content')
    <div class="col-xl-12 col-md-12">
        <div class="site-card">
            <div class="site-card-body table-responsive">
                <div class="site-table table-responsive">
                    @include('backend.deposit.include.__filter')
                    <table class="table">
                        <thead>
                        <tr>
                            @include('backend.filter.th',['label' => 'Date','field' => 'created_at'])
                            @include('backend.filter.th',['label' => 'User','field' => 'user'])
                            @include('backend.filter.th',['label' => 'Transaction ID','field' => 'tnx'])
                            @include('backend.filter.th',['label' => 'Amount','field' => 'amount'])
                            @include('backend.filter.th',['label' => 'Charge','field' => 'charge'])
                            @include('backend.filter.th',['label' => 'Gateway','field' => 'method'])
                            @include('backend.filter.th',['label' => 'Status','field' => 'status'])
                            <th>{{ __('Action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($deposits as $deposit)
                            <tr>
                                <td>
                                    {{ $deposit->created_at }}
                                </td>
                                <td>
                                    @include('backend.transaction.include.__user', ['id' => $deposit->user_id, 'name' => $deposit->user->username])
                                </td>
                                <td>{{ safe($deposit->tnx) }}</td>
                                <td>
                                    @include('backend.transaction.include.__txn_amount', ['txnType' => $deposit->type, 'amount' => $deposit->final_amount, 'currency' => $deposit->pay_currency])
                                </td>
                                <td>
                                    {{ safe($deposit->charge.' '.setting('site_currency', 'global')) }}
                                </td>
                                <td>
                                    {{ safe($deposit->method) }}
                                </td>
                                <td>
                                    @include('backend.transaction.include.__txn_status', ['txnStatus' => $deposit->status->value])
                                </td>
                                <td>
                                    @include('backend.deposit.include.__action', ['id' => $deposit->id])
                                </td>
                            </tr>
                        @empty
                        <td colspan="8" class="text-center">{{ __('No Data Found!') }}</td>
                        @endforelse
                        </tbody>
                    </table>

                    {{ $deposits->links('backend.include.__pagination') }}
                </div>
            </div>
        </div>
        <!-- Modal for Pending Deposit Approval -->
        @can('deposit-action')
            <div
                class="modal fade"
                id="deposit-action-modal"
                tabindex="-1"
                aria-labelledby="editPendingDepositModalLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-lg modal-dialog-centered manual-deposit-modal">
                    <div class="modal-content site-table-modal">
                        <div class="modal-body popup-body">
                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"
                            ></button>
                            <div class="popup-body-text deposit-action">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        <!-- Modal for Pending Deposit Approval -->
    </div>
@endsection
@section('script')
    <script>
        (function ($) {
            "use strict";

            let loader = '<div class="text-center"><img src="{{ asset('front/images/loader.gif') }}" width="100"><h5>{{ __('Please wait') }}...</h5></div>';

            //send mail modal form open
            $('body').on('click', '#deposit-action', function () {
                $('.deposit-action').html(loader);

                var id = $(this).data('id');
                var url = '{{ route("admin.deposit.action",":id") }}';
                url = url.replace(':id', id);
                $.get(url, function (data) {
                    $('.deposit-action').html(data)
                    imagePreview()
                });

                $('#deposit-action-modal').modal('toggle');
            })


        })(jQuery);
    </script>
@endsection
