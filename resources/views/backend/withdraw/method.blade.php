@extends('backend.withdraw.index')
@section('title')
    {{ __('Withdraw Methods') }}
@endsection

@section('withdraw_content')
    <div class="col-xl-12 col-md-12">
        <div class="site-table table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Logo') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Currency') }}</th>
                        <th>{{ __('Limits') }}</th>
                        <th>{{ __('Charge') }}</th>
                        <th>{{ __('Processing') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Manage') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawMethods as $method)
                        @php
                            $icon = $method->icon;
                            if ($method->gateway_id !== null && $method->icon == '') {
                                $icon = $method->gateway->logo;
                            }
                        @endphp
                        <tr>
                            <td>
                                <img height="28" src="{{ asset($icon) }}" alt="{{ $method->name }}">
                            </td>
                            <td>{{ $method->name }}</td>
                            <td><span class="site-badge badge-primary text-capitalize">{{ $method->type }}</span></td>
                            <td>{{ $method->currency }}</td>
                            <td>{{ $method->min_withdraw }} - {{ $method->max_withdraw }} {{ $currency }}</td>
                            <td>
                                {{ $method->charge }}
                                {{ $method->charge_type === 'percentage' ? '%' : $currency }}
                            </td>
                            <td>
                                @if($method->type === 'manual')
                                    {{ $method->required_time }} {{ ucfirst($method->required_time_format) }}
                                @else
                                    {{ __('Instant') }}
                                @endif
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
                                   href="{{ route('admin.withdraw.method.edit',['type' => strtolower($type),'id' => $method->id]) }}">
                                    <i data-lucide="settings-2"></i>
                                </a>
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
    </div>
@endsection
