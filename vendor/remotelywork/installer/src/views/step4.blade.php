@extends('installer::layouts.installer')

@section('title','Import Database')
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
        <div class="single-step">
            <h4>Step 5</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/5.png') }}" alt=""></div>
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

        <form action="{{ route('install.import.sql') }}" method="post">
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

            <div class="single-box mb-0">
                <button type="submit" class="submit-btn w-100" type="button">Import Database -> ( Admin Setup )</button>
            </div>
        </form>
    </div>
</div>
@endsection
