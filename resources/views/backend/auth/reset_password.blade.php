@extends('backend.auth.index')
@section('title')
    {{ __('Reset Password') }}
@endsection
@section('auth-content')
    <div class="login">
        <div class="side-img primary-overlay" style="background: url({{ asset(setting('login_bg','global')) }}) no-repeat center center;">
            <div class="title">
                <h3>{{ __('Set New Password') }}</h3>
            </div>
        </div>
        <div class="login-content">
            @php
                $height = setting('site_logo_height','global') == 'auto' ? 'auto' : setting('site_logo_height','global').'px';
                $width = setting('site_logo_width','global') == 'auto' ? 'auto' : setting('site_logo_width','global').'px';
            @endphp
            <div class="logo">
                <a href="{{ route('home') }}">
                    <img src="{{asset(setting('site_logo','global') )}}" style="height:{{ $height }};width:{{ $width }}" alt="{{asset(setting('site_title','global') )}}"/>
                </a>
            </div>
            <div class="auth-body">
                <div class="auth-header">
                    <h2 class="auth-title">{{ __('Choose a new password') }}</h2>
                    <p class="auth-subtitle">{{ __('Use a strong password to secure your admin account.') }}</p>
                </div>

                <form action="{{ route('admin.reset.password.submit') }}" method="post">
                    @csrf
                    <input type="hidden" name="token" value="{{ request('token') }}">

                    <div class="single-box">
                        <label class="box-label">{{ __('Admin Email') }}</label>
                        <input type="email" name="email" class="box-input" placeholder="{{ __('Admin Email') }}" required />
                        @if ($errors->has('email'))
                            <span class="auth-feedback">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    <div class="single-box">
                        <label class="box-label">{{ __('New Password') }}</label>
                        <input type="password" name="password" class="box-input" placeholder="{{ __('New Password') }}" required />
                        @if ($errors->has('password'))
                            <span class="auth-feedback">{{ $errors->first('password') }}</span>
                        @endif
                    </div>

                    <div class="single-box">
                        <label class="box-label">{{ __('Confirm Password') }}</label>
                        <input type="password" name="password_confirmation" class="box-input" placeholder="{{ __('Confirm Password') }}" required />
                        @if ($errors->has('password_confirmation'))
                            <span class="auth-feedback">{{ $errors->first('password_confirmation') }}</span>
                        @endif
                    </div>

                    <div class="single-box auth-actions">
                        <button class="site-btn primary-btn" type="submit">{{ __('Reset Password') }}</button>
                        <a href="{{ route('admin.login-view') }}" class="link">{{ __('Back to login') }}</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
