@extends('backend.deposit.index')
@section('title')
    {{ __(ucwords($type).' Deposit Method') }}
@endsection

@section('deposit_content')
    <div class="col-xl-12 col-md-12">
        <div class="site-table table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Logo') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Method Code') }}</th>
                        <th>{{ __('Currency') }}</th>
                        <th>{{ __('Limits') }}</th>
                        <th>{{ __('Charge') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Manage') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($depositMethods as $method)
                        <tr>
                            <td>
                                <img height="28" src="{{ asset($method->logo ?? $method->gateway->logo) }}" alt="{{ $method->name }}">
                            </td>
                            <td>{{ $method->name }}</td>
                            <td><span class="site-badge badge-primary">{{ $method->gateway_code }}</span></td>
                            <td>{{ $method->currency }}</td>
                            <td>{{ $method->minimum_deposit }} - {{ $method->maximum_deposit }} {{ $currency }}</td>
                            <td>
                                {{ $method->charge }}
                                {{ $method->charge_type === 'percentage' ? '%' : $currency }}
                            </td>
                            <td>
                                @if($method->status)
                                    <div class="site-badge success">{{ __('Activated') }}</div>
                                @else
                                    <div class="site-badge pending">{{ __('Deactivated') }}</div>
                                @endif
                            </td>
                            <td>
                                <a class="round-icon-btn primary-btn"
                                   href="{{ route('admin.deposit.method.edit',['type' => strtolower($type),'id' => $method->id]) }}">
                                    <i data-lucide="settings-2"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('No Data Found!') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
