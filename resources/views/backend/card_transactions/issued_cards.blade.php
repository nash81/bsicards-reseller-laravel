@extends('backend.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-title">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="title-content">
                        <h2 class="title">Issued Cards</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="site-table table-responsive">
                    <form action="{{ request()->url() }}" method="get" class="mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-5">
                                <label for="search" class="form-label">{{ __('Search') }}</label>
                                <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('Search in results...') }}">
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label for="perPage" class="form-label">{{ __('Per Page') }}</label>
                                <select name="perPage" id="perPage" class="form-select">
                                    @foreach([15, 30, 45, 60] as $perPage)
                                        <option value="{{ $perPage }}" @selected((int) request('perPage', 15) === $perPage)>{{ $perPage }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i data-lucide="search"></i>
                                    {{ __('Filter') }}
                                </button>
                                @if(request()->query())
                                    <a href="{{ request()->url() }}" class="btn btn-outline-secondary w-100">{{ __('Reset') }}</a>
                                @endif
                            </div>
                        </div>
                    </form>
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                @include('backend.filter.th', ['label' => 'Card ID', 'field' => 'cardid'])
                                @include('backend.filter.th', ['label' => 'User Email', 'field' => 'useremail'])
                                @include('backend.filter.th', ['label' => 'Brand', 'field' => 'brand'])
                                @include('backend.filter.th', ['label' => 'Last Four', 'field' => 'lastfour'])
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cards as $card)
                                <tr>
                                    <td>{{ $card['cardid'] }}</td>
                                    <td>{{ $card['useremail'] }}</td>
                                    <td>{{ $card['brand'] }}</td>
                                    <td>{{ $card['lastfour'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No cards found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $cards->links('backend.include.__pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('single-script')
<script>
    (function ($) {
        'use strict';
        $('#perPage').on('change', function () {
            $(this).closest('form').trigger('submit');
        });
    })(jQuery);
</script>
@endpush
