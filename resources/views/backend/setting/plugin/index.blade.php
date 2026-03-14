@extends('backend.setting.index')
@section('setting-title')
    {{ __('Plugin Settings') }}
@endsection
@section('title')
    {{ __('Plugin Settings') }}
@endsection
@section('setting-content')
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="site-card">
            <div class="site-card-header">
                <h3 class="title">{{ $title }}</h3>
                @if($isLink)
                    <div class="card-header-links">
                        <a href="{{ route('admin.settings.notification.tune') }}" class="card-header-link new-referral"
                           type="button" data-type="investment">
                            <i data-lucide="volume-1"></i>{{ __('Set Tune') }}</a>
                    </div>

                @endif

            </div>
            <div class="site-card-body p-0">
                <div class="alert alert-info m-3 mb-0" style="border-left: 4px solid #0073e6;">
                    <i data-lucide="info"></i> {{ __('You can') }}
                    <strong>{{ __('Enable or Disable') }}</strong> {{ __('any of the plugin') }}
                </div>
                <div class="list-group list-group-flush">
                    @foreach($plugins as $plugin)
                        <div class="list-group-item d-flex align-items-center justify-content-between py-3">
                            <div class="d-flex align-items-center gap-3 flex-grow-1">
                                <div class="plugin-icon-wrapper" style="width: 48px; height: 48px; border-radius: 8px; background: #f6f9fc; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <img src="{{ asset($plugin->icon) }}" alt="{{ $plugin->name }}" style="max-width: 32px; max-height: 32px;"/>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1" style="font-size: 15px; font-weight: 600; color: #0a2540;">{{ $plugin->name }}</h6>
                                    <p class="mb-0 text-muted" style="font-size: 13px;">{{ $plugin->description }}</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($plugin->status)
                                    <span class="site-badge success">{{ __('Active') }}</span>
                                @else
                                    <span class="site-badge pending">{{ __('Inactive') }}</span>
                                @endif
                                <button type="button" class="round-icon-btn primary-btn editPlugin" data-id="{{$plugin->id}}" data-bs-toggle="tooltip" title="Configure">
                                    <i data-lucide="settings-2"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Edit Plugin -->
    <div
        class="modal fade"
        id="editPlugin"
        tabindex="-1"
        aria-labelledby="editPluginModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content site-table-modal">
                <div class="modal-body popup-body">
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                    <div class="popup-body-text edit-plugin-section">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for Edit Plugin-->
@endsection
@section('script')

    <script>
        $('.editPlugin').on('click', function (e) {
            "use strict"
            var id = $(this).data('id');
            $('.edit-plugin-section').empty();

            var url = '{{ route("admin.settings.plugin.data",":id") }}';
            url = url.replace(':id', id);
            $.get(url, function ($data) {
                $('.edit-plugin-section').append($data)
                // Lucide Icons Activation
                lucide.createIcons();

                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

                $('#editPlugin').modal('show');
            })

        })
    </script>

@endsection
