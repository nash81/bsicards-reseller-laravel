@extends('backend.layouts.app')
@section('title')
    {{ __('All Notifications') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('All Notifications') }}</h2>
                            <a href="{{ route('admin.read-notification', 0) }}" class="title-btn"><i data-lucide="check"></i> Mark all read</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="site-card">
                        <div class="site-card-body">
                            <div class="notification-list admin-listview">
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
                                            <a href="{{ route('admin.read-notification', $notification->id) }}" class="site-btn-sm primary-btn notification-view-btn">
                                                <i data-lucide="eye"></i>
                                                <span>{{ __('View') }}</span>
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center">{{ __('No Data Found!') }}</div>
                                @endforelse
                            </div>

                            {{ $notifications->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
