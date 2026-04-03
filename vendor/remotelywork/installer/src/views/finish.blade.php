@extends('installer::layouts.installer')

@section('title','Installation Finished')
@section('steps')
<div class="top">
    <div class="step-progress">
        <div class="single-step finished">
            <h4>Step 1</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/1.1.png') }}" alt=""></div>
        </div>
        <div class="single-step finished">
            <h4>Step 2</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/2.2.png') }}" alt=""></div>
        </div>
        <div class="single-step finished">
            <h4>Step 3</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/3.3.png') }}" alt=""></div>
        </div>
        <div class="single-step finished">
            <h4>Step 4</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/4.4.png') }}" alt=""></div>
        </div>
        <div class="single-step finished">
            <h4>Step 5</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/5.5.png') }}" alt=""></div>
        </div>
        <div class="single-step finished">
            <h4>Step 6</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/6.6.png') }}" alt=""></div>
        </div>
    </div>
    <h3>@yield('title')</h3>
</div>
@endsection
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-md-12">
            <div class="row content">
                <div class="col-xl-4 col-md-6">
                    <div class="single-box">
                        <a href="{{ route('admin.dashboard') }}" class="submit-btn w-100" type="button">Admin Dashboard</a>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="single-box">
                        <a href="{{ route('user.dashboard') }}" class="submit-btn w-100" type="button">User Dashboard</a>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="single-box">
                        <a href="{{ url('/') }}" class="submit-btn w-100" type="button">Landing Page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
