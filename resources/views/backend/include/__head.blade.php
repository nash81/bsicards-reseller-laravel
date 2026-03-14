<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }} ">
    <link
        rel="shortcut icon"
        href="{{ asset(setting('site_favicon','global')) }}"
        type="image/x-icon"
    />
    <link rel="icon" href="{{ asset(setting('site_favicon','global')) }}" type="image/x-icon"/>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('global/css/fontawesome.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('backend/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('backend/css/animate.css') }}"/>
    <link rel="stylesheet" href="{{ asset('global/css/nice-select.css') }}"/>
    <link rel="stylesheet" href="{{ asset('global/css/datatables.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('global/css/simple-notify.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('global/css/daterangepicker.css') }}"/>
    <link rel="stylesheet" href="{{ asset('backend/css/summernote-lite.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('global/css/select2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('global/css/custom.css?v=1.0') }}"/>

    <!-- Professional Admin Revamp Styles -->
    <link rel="stylesheet" href="{{ asset('backend/css/admin-revamp.css?v=2.0') }}"/>

    @php
        $isAdminAuthRoute = request()->routeIs('admin.login-view')
            || request()->routeIs('admin.forget.password.now')
            || request()->routeIs('admin.reset.password.now');
    @endphp
    @if($isAdminAuthRoute)
        <link rel="stylesheet" href="{{ asset('backend/css/auth.css?v=1.1') }}"/>
    @endif

    @yield('style')

    <title> @yield('title') - {{ setting('site_title', 'global') }}</title>
</head>
