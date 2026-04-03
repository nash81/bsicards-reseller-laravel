@extends('installer::layouts.installer')

@section('title','Admin Setup')
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
        <div class="single-step">
            <h4>Step 6</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/6.png') }}" alt=""></div>
        </div>
    </div>
    <h3>@yield('title')</h3>
    <p>Just make sure to provide all the correct informations to install the application</p>
</div>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-md-12">

        <form action="{{ route('install.admin.setup') }}" method="post">
            @csrf

            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif
            <div class="row content">
                <div class="col-xl-6 col-md-12">
                    <div class="single-box">
                        <label for="email" class="form-label">Admin Email<span style="color: red;">*</span></label>
                        <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" required>
                        @error('email')
                        <div class="text text-danger">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="col-xl-6 col-md-12">
                    <div class="single-box">
                        <label for="password" class="form-label">Admin Password<span style="color: red;">*</span></label>
                        <input type="password" class="form-control" name="password" id="password" required>
                        @error('password')
                        <div class="text text-danger">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="col-xl-12 col-md-12">
                    <div class="single-box mb-0">
                        <button type="submit" class="submit-btn w-100" type="button">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
