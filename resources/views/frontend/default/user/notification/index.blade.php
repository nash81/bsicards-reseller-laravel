@extends('frontend::layouts.user')
@section('title')
    {{ __('All Notifications') }}
@endsection
@section('content')
    <div class="row">
        @include('frontend::user.setting.include.__settings_nav')
        <div class="col-xl-12">
            <div class="site-card">
                <div class="site-card-header">
                    <h3 class="title-small">{{ __('All Notifications') }}</h3>
                    <a href="{{ route('user.read-notification', 0) }}" class="card-header-link">
                        <i data-lucide="check"></i>{{ __('Mark all read') }}
                    </a>
                </div>
                <div class="site-card-body p-0">
                    <div class="notification-list user-notification-listview">
                        @forelse($notifications as $notification)
                            <div @class(['single-list', 'read' => $notification->read])>
                                <div class="cont">
                                    <div class="icon"><i data-lucide="{{ $notification->icon }}"></i></div>
                                    <div class="contents">
                                        <div class="title">{{ $notification->title }}</div>
                                        <div class="time">{{ $notification->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <div class="link">
                                    <a href="{{ route('user.read-notification', $notification->id) }}" class="site-btn-sm primary-btn notification-view-btn">
                                        <i data-lucide="eye"></i>
                                        <span>{{ __('View') }}</span>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">{{ __('No Data Found!') }}</div>
                        @endforelse
                    </div>
                </div>
                @if($notifications->hasPages())
                <div class="site-card-body pt-0">
                    {{ $notifications->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
