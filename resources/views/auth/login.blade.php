<!DOCTYPE html>
<html lang="ar" dir="rtl" data-bs-theme="light" data-skin="shadcn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.auth.login_title') }} | {{ __('messages.app_name') }}</title>
    <link rel="shortcut icon" href="{{ asset('dashboard/assets/images/favicon.ico') }}">

    <script src="{{ asset('dashboard/assets/js/config.js') }}"></script>

    <link href="{{ asset('dashboard/assets/css/vendors.min.css') }}" rel="stylesheet">
    <link href="{{ asset('dashboard/assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('dashboard/assets/css/rtl.css') }}" rel="stylesheet">

    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                radial-gradient(900px 400px at 85% -10%, rgba(28, 111, 216, .10), transparent 60%),
                radial-gradient(700px 380px at 10% 110%, rgba(28, 111, 216, .08), transparent 60%),
                var(--ins-body-bg, #f6f7f9);
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            border: 1px solid var(--ins-border-color, #e5e7eb);
            border-radius: 1rem;
            box-shadow: 0 20px 45px -18px rgba(15, 23, 42, .18);
            overflow: hidden;
        }
        .auth-card .card-body { padding: 2.25rem 2rem; }
        .brand-badge {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #1c2434 0%, #3b4a63 100%);
            box-shadow: 0 10px 22px -10px rgba(28, 36, 52, .55);
        }
        .field-icon-group { position: relative; }
        .field-icon-group .form-control {
            padding-right: 2.75rem; /* مساحة أيقونة الحقل في اليمين */
            padding-left: 2.6rem;   /* مساحة زر العين في اليسار */
            height: 48px;
        }
        .field-icon-group .field-icon {
            position: absolute;
            top: 50%;
            right: .9rem;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            display: inline-flex;
        }
        .field-icon-group .toggle-eye {
            position: absolute;
            top: 50%;
            left: .35rem;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #94a3b8;
            padding: .45rem;
            border-radius: .5rem;
            display: inline-flex;
            cursor: pointer;
            transition: color .15s ease, background-color .15s ease;
        }
        .field-icon-group .toggle-eye:hover { color: #1c2434; background: #f1f5f9; }
        .field-icon-group .form-control.is-invalid {
            background-image: none; /* أيقونة الخطأ الافتراضية تتعارض مع أيقونة الحقل */
        }
        .btn-login {
            height: 48px;
            font-size: 1rem;
            border-radius: .65rem;
        }
        .auth-footer {
            text-align: center;
            padding: 0 2rem 1.5rem;
            color: #94a3b8;
            font-size: .8rem;
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="card auth-card mb-0">
            <div class="card-body">

                <div class="text-center mb-4">
                    <span class="brand-badge mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/>
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                            <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/>
                            <path d="M2 7h20"/>
                            <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/>
                        </svg>
                    </span>
                    <h4 class="fw-bold mb-1">{{ __('messages.app_name') }}</h4>
                    <p class="text-muted mb-0">{{ __('messages.auth.login_subtitle') }}</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                            <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium">{{ __('messages.auth.email') }}</label>
                        <div class="field-icon-group">
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="name@example.com" dir="ltr" style="text-align: start;"
                                   required autofocus autocomplete="username">
                            <span class="field-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-medium">{{ __('messages.auth.password') }}</label>
                        <div class="field-icon-group">
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="••••••••"
                                   required autocomplete="current-password">
                            <span class="field-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <button type="button" class="toggle-eye" id="toggle-password"
                                    aria-label="{{ __('messages.auth.show_password') }}" title="{{ __('messages.auth.show_password') }}">
                                <svg id="eye-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg id="eye-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.4 10.4 0 0 1 12 5c6.5 0 10 7 10 7a13.2 13.2 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.5 13.5 0 0 0 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login fw-semibold" id="login-button">
                            {{ __('messages.auth.login') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="auth-footer">
                {{ __('messages.app_name') }} &copy; {{ now()->year }}
            </div>
        </div>
    </div>

    <script>
        document.getElementById('toggle-password').addEventListener('click', function () {
            const input = document.getElementById('password');
            const show = input.type === 'password';

            input.type = show ? 'text' : 'password';
            document.getElementById('eye-show').style.display = show ? 'none' : '';
            document.getElementById('eye-hide').style.display = show ? '' : 'none';
            this.title = show ? @js(__('messages.auth.hide_password')) : @js(__('messages.auth.show_password'));
            this.setAttribute('aria-label', this.title);
        });

        document.querySelector('form').addEventListener('submit', function () {
            const button = document.getElementById('login-button');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>' + @js(__('messages.auth.logging_in'));
        });
    </script>
</body>
</html>
