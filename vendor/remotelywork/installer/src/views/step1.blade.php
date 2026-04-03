@extends('installer::layouts.installer')

@section('title','Server Requirements')
@section('steps')
<div class="top">
    <div class="step-progress">
        <div class="single-step finished">
            <h4>Step 1</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/1.1.png') }}" alt=""></div>
        </div>
        <div class="single-step">
            <h4>Step 2</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/2.png') }}" alt=""></div>
        </div>
        <div class="single-step">
            <h4>Step 3</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/3.png') }}" alt=""></div>
        </div>
        <div class="single-step">
            <h4>Step 4</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/4.png') }}" alt=""></div>
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
@php
$enabledExtensions = [];
@endphp
<div class="row justify-content-center">
    <div class="col-xl-8 col-md-12">
        <div class="row content">
            <div class="col-xl-6 col-md-12">
                <div class="single-box">
                    <div class="icon-check-list">
                        <div class="one" style="margin-bottom: 15px; font-size: 14px; font-weight: 700;">
                            @if(version_compare(phpversion(),config('installer.min_php'),'>='))
                            <img style="height: 20px; margin-right: 7px" src="{{ asset('global/installer/icons/tick.png') }}" alt="">
                            @else
                            <img style="height: 20px; margin-right: 7px" src="{{ asset('global/installer/icons/close.png') }}" alt="">
                            @endif
                            PHP {{ config('installer.min_php') }} or Higher
                        </div>
                        @foreach ($requiredExtensions as $extension => $extensionName)
                        <div class="one" style="margin-bottom: 15px; font-size: 14px; font-weight: 700;">
                            @if(in_array($extension,$loadedExtensions))
                            @php
                                $enabledExtensions[] = $extension;
                            @endphp
                            <img style="height: 20px; margin-right: 7px" src="{{ asset('global/installer/icons/tick.png') }}" alt="">
                            @else
                            <img style="height: 20px; margin-right: 7px" src="{{ asset('global/installer/icons/close.png') }}" alt="">
                            @endif
                            {{ $extensionName }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-xl-12 col-md-12">
                <div class="single-box mb-0">
                    @if(count($requiredExtensions) == count($enabledExtensions) && version_compare(phpversion(),config('installer.min_php'),'>='))
                    <a href="{{ route('install.step.two') }}" class="submit-btn w-100" type="button">Next Step -> ( License Activation )</a>
                    @else
                    <div class="alert alert-danger"><b>Attention:</b> Your server is not compatiable for our system! Firstly, fullfill the server requirements.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
