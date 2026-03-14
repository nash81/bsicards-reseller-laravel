@extends('backend.layouts.app')
@section('title')
    {{ $title }}
@endsection
@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title">
                <div class="title-content">
                    <h2 class="title">{{ $title }}</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="site-table">
                        <form action="{{ request()->url() }}" method="get">
                            <div class="table-filter">
                                <div class="filter">
                                    <div class="search">
                                        <input type="text" id="search" name="query" value="{{ request('query') }}"
                                            class="form-control"
                                            placeholder="Search User..." />
                                    </div>
                                    <select name="email_status" id="email_status" class="form-select">
                                        <option value="" selected>{{ __('Email Status') }}</option>
                                        <option value="verified" {{ request('email_status') == 'verified' ? 'selected' : '' }}>{{ __('Verified') }}</option>
                                        <option value="unverified" {{ request('email_status') == 'unverified' ? 'selected' : '' }}>{{ __('Unverified') }}</option>
                                    </select>
                                    <select name="kyc_status" id="kyc_status" class="form-select">
                                        <option value="" selected>{{ __('KYC Status') }}</option>
                                        <option value="1" {{ request('kyc_status') == '1' ? 'selected' : '' }}>{{ __('Verified') }}</option>
                                        <option value="0" {{ request('kyc_status') == '0' ? 'selected' : '' }}>{{ __('Unverified') }}</option>
                                    </select>

                                    <select name="status" id="status" class="form-select">
                                        <option value="" selected>{{ __('Filter Status') }}</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('Disabled') }}</option>
                                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                                    </select>
                                    <button type="submit" class="apply-btn"><i data-lucide="search"></i>{{ __('Filter') }}</button>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="data-table mb-0">
                                <thead>
                                <tr>
                                    <th>{{ __('Avatar') }}</th>
                                    @include('backend.filter.th',['label' => 'User','field' => 'username'])
                                    @include('backend.filter.th',['label' => 'Email','field' => 'email'])
                                    @include('backend.filter.th',['label' => 'Balance','field' => 'balance'])
                                    <th>{{ __('Payback') }}</th>
                                    <th>{{ __('Email Status') }}</th>
                                    <th>{{ __('KYC') }}</th>
                                    @include('backend.filter.th',['label' => 'Status','field' => 'status'])
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>
                                            @include('backend.user.include.__avatar', ['avatar' => $user->avatar, 'first_name' => $user->first_name, 'last_name' => $user->last_name])
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.user.edit',$user->id) }}" class="link">{{ Str::limit($user->username,15) }}</a>
                                        </td>
                                        <td>{{ Str::limit($user->email,20) }}</td>
                                        <td><strong>{{ $currencySymbol.$user->balance }}</strong></td>
                                        <td>{{ $currencySymbol.$user->total_profit }}</td>
                                        <td>
                                            @if($user->email_verified_at != null)
                                                <div class="site-badge success">{{ __('Verified') }}</div>
                                            @else
                                                <div class="site-badge pending">{{ __('Unverified') }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @include('backend.user.include.__kyc' , ['kyc' => $user->kyc])
                                        </td>
                                        <td>
                                            @include('backend.user.include.__status', ['status' => $user->status])
                                        </td>
                                        <td class="text-end">
                                            @include('backend.user.include.__action', ['user' => $user])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">{{ __('No Data Found!') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $users->links('backend.include.__pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function ($) {
            "use strict";

            //send mail modal form open
            $('body').on('click', '.send-mail', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                $('#name').html(name);
                $('#userId').val(id);
                $('#sendEmail').modal('toggle')
            })

            // Delete
            $('body').on('click', '#deleteModal', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');

                $('#data-name').html(name);
                var url = '{{ route("admin.user.destroy", ":id") }}';
                url = url.replace(':id', id);
                $('#deleteForm').attr('action', url);
                $('#delete').modal('toggle')

            });

        })(jQuery);
    </script>
@endsection
