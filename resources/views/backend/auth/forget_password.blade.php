@extends('backend.auth.index')
@section('title')
    {{ __('Reset Password') }}
@endsection
@section('auth-content')
    <div class="login">
        <div class="side-img primary-overlay" style="background: url({{asset(setting('login_bg','global'))}}) no-repeat center center;">
            <div class="title">
                <h3>{{ __('Password Recovery') }}</h3>
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
                    <h2 class="auth-title">{{ __('Reset your password') }}</h2>
                    <p class="auth-subtitle">{{ __('Enter your admin email to receive the reset link.') }}</p>
                </div>

                <form action="{{ route('admin.forget.password.submit') }}" method="post">
                    @csrf

                    @if ($errors->has('email'))
                        <span class="auth-feedback">{{ $errors->first('email') }}</span>
                    @endif

                    <div class="single-box">
                        <label class="box-label">{{ __('Admin Email') }}</label>
                        <input type="email" name="email" class="box-input" required />
                    </div>

                    <div class="single-box auth-actions">
                        <button class="site-btn primary-btn" type="submit">{{ __('Send Reset Link') }}</button>
                        <a href="{{ route('admin.login-view') }}" class="link">{{ __('Back to login') }}</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
