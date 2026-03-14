@extends('backend.layouts.app')

@section('title')
    {{ $pageTitle }}
@endsection

@section('content')
    @include('backend.bsicards.partials.list')
@endsection

