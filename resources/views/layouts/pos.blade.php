<!DOCTYPE html>
<html lang="ar" dir="rtl" data-bs-theme="light" data-skin="shadcn" data-sidenav-color="light" data-topbar-color="light" data-layout-position="fixed" data-sidenav-user="true">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('messages.app_name')) | {{ __('messages.app_name') }}</title>
    <link rel="shortcut icon" href="{{ asset('dashboard/assets/images/favicon.ico') }}">

    <script src="{{ asset('dashboard/assets/js/config.js') }}"></script>
    <script>
        // طي القائمة الجانبية معطّل: تجاهُل أي حالة "collapse" محفوظة من جلسات سابقة.
        if (document.documentElement.getAttribute('data-sidenav-size') === 'collapse') {
            document.documentElement.setAttribute('data-sidenav-size', 'default');
        }
    </script>

    <link href="{{ asset('dashboard/assets/css/vendors.min.css') }}" rel="stylesheet">
    <link href="{{ asset('dashboard/assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('dashboard/assets/css/rtl.css') }}?v={{ filemtime(public_path('dashboard/assets/css/rtl.css')) }}" rel="stylesheet">
    <link href="{{ asset('dashboard/assets/css/custom.css') }}?v={{ filemtime(public_path('dashboard/assets/css/custom.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="wrapper">

        @include('layouts.partials.sidenav')

        <div class="content-page">

            @include('layouts.partials.topbar')

            <div class="container-fluid">

                <div class="page-title-head">
                    <span class="page-icon">
                        <i data-lucide="@yield('page-icon', 'panel-top')" style="width:20px;height:20px;"></i>
                    </span>
                    <div class="flex-grow-1">
                        <h4 class="fs-lg fw-semibold mb-0">@yield('title')</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-muted text-decoration-none">{{ __('messages.app_name') }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @yield('title-actions')
                    </div>
                </div>

                @include('layouts.partials.flash')

                @yield('content')
            </div>

            <footer class="footer">
                <div class="container-fluid d-flex justify-content-center">
                    <span>{{ __('messages.app_name') }} &copy; {{ now()->year }}</span>
                </div>
            </footer>
        </div>
    </div>

    @include('partials.image-lightbox')

    <script src="{{ asset('dashboard/assets/js/vendors.min.js') }}"></script>
    <script src="{{ asset('dashboard/assets/js/app.js') }}"></script>
    @stack('scripts')
    <script src="{{ asset('dashboard/assets/js/alpine.min.js') }}" defer></script>
</body>
</html>
