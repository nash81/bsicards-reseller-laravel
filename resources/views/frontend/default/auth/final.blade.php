@extends('frontend::layouts.auth')

@section('title')
    {{ __('Finish Up') }}
@endsection

@section('content')
    <!-- Register Section -->
    <div class="half-authpage">
        <div class="authOne">
            <div class="auth-contents">
                @php
                    $height = setting('site_logo_height','global') == 'auto' ? 'auto' : setting('site_logo_height','global').'px';
                    $width = setting('site_logo_width','global') == 'auto' ? 'auto' : setting('site_logo_width','global').'px';
                @endphp
                <div class="logo">
                    <a href="{{ route('home')}}"><img src="{{ asset(setting('site_logo','global')) }}" style="height:{{ $height }};width:{{ $width }};max-width:none" alt=""></a>
                    <div class="no-user-header">
                        @if(setting('language_switcher'))
                            <div class="language-switcher">
                                <select class="langu-swit small" name="language" onchange="window.location.href=this.options[this.selectedIndex].value;">
                                    @foreach(\App\Models\Language::where('status',true)->get() as $lang)
                                        <option
                                            value="{{ route('language-update',['name'=> $lang->locale]) }}" @selected( app()->getLocale() == $lang->locale )>{{$lang->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="color-switcher">
                            <img class="light-icon" src="{{ asset('front/images/icons/sun.png') }}" alt="">
                            <img class="dark-icon" src="{{ asset('front/images/icons/moon.png') }}" alt="">
                        </div>
                    </div>
                </div>
                <div class="contents">
                    <div class="content finish-wrapper">
                        <div class="finish-icon-wrap centered mb-3">
                            <span class="finish-icon"><i data-lucide="badge-check"></i></span>
                        </div>
                        <h3 class="centered mb-2">{{ __('You are all set!') }}</h3>
                        <p class="finish-subtitle centered">{{ __('Your account has been created successfully. Start exploring your dashboard now.') }}</p>

                        <div class="finish-highlight mb-4">
                            @if(setting('referral_signup_bonus','permission'))
                                <strong>{{ __('Congratulations! You have earned :bonus by signing up.',['bonus' => $currencySymbol.setting('signup_bonus','fee')]) }}</strong>
                            @else
                                <strong>{{ __('Congratulations! You made it.') }}</strong>
                            @endif
                        </div>

                        <div class="inputs centered mb-2">
                            <a href="{{ route('user.dashboard') }}" class="site-btn primary-btn w-100"><i data-lucide="layout-dashboard"></i>{{ __('Go to Dashboard') }}</a>
                        </div>
                        <div class="inputs centered">
                            <a href="{{ route('home') }}" class="finish-home-link">{{ __('Back to Home') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Register Section End -->
@endsection

@push('style')
<style>
    .modern-banking-theme .finish-wrapper {
        margin: 0 auto;
        max-width: 520px;
        padding: 28px 24px;
        border: 1px solid var(--bank-border);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06);
    }

    .modern-banking-theme .finish-icon {
        width: 64px;
        height: 64px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--bank-primary), #14b8a6);
        color: #ffffff;
    }

    .modern-banking-theme .finish-icon i {
        width: 30px;
        height: 30px;
    }

    .modern-banking-theme .finish-wrapper .finish-subtitle {
        color: var(--bank-muted);
        margin-bottom: 18px;
    }

    .modern-banking-theme .finish-wrapper .finish-highlight {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 10px;
        padding: 14px;
        text-align: center;
        color: #0f172a;
        font-size: 0.95rem;
    }

    .modern-banking-theme .finish-home-link {
        color: var(--bank-primary);
        font-weight: 600;
        text-decoration: none;
    }

    .modern-banking-theme .finish-home-link:hover {
        text-decoration: underline;
    }
</style>
@endpush

@push('js')
<script type="text/javascript" src="{{ asset('front/js/confetti.min.js') }}"></script>
<script>
    'use strict';

    // start
    const start = () => {
        setTimeout(function() {
            confetti.start()
        }, 1000); // 1000 is time that after 1 second start the confetti ( 1000 = 1 sec)

        setTimeout(function() {
            confetti.stop()
        }, 7000);
    };

    start();
</script>
@endpush
