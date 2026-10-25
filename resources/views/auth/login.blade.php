@extends('layouts.login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
            <!-- Brand Link to Welcome / Home -->
            <div class="text-center mb-4">
                <a href="{{ url('/') }}" class="text-decoration-none text-reset d-inline-block">
                    <h1 class="login-brand-title">MQMS</h1>
                    <div class="login-brand-subtitle">Material &amp; Quantity Management System</div>
                </a>
            </div>

            <!-- Login Card -->
            <div class="card login-card">
                <div class="card-header login-card-header d-flex align-items-center justify-content-between">
                    <span class="d-inline-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock" style="color: var(--app-primary);"></i>
                        <span>{{ __('Login') }}</span>
                    </span>
                    <span class="badge login-system-badge">OPERATOR ACCESS</span>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label login-form-label">{{ __('Email Address') }}</label>
                            <div class="input-group">
                                <span class="input-group-text login-input-icon"><i class="bi bi-envelope"></i></span>
                                <input id="email" type="email" class="form-control login-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@company.com">
                            </div>

                            @error('email')
                                <span class="invalid-feedback d-block mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label login-form-label mb-0">{{ __('Password') }}</label>
                                @if (Route::has('password.request'))
                                    <a class="login-link small" href="{{ route('password.request') }}">
                                        {{ __('Forgot Password?') }}
                                    </a>
                                @endif
                            </div>
                            <div class="input-group">
                                <span class="input-group-text login-input-icon"><i class="bi bi-key"></i></span>
                                <input id="password" type="password" class="form-control login-input @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                            </div>

                            @error('password')
                                <span class="invalid-feedback d-block mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input login-checkbox" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label login-checkbox-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-login">
                                <span>{{ __('Login') }}</span>
                                <i class="bi bi-box-arrow-in-right"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer login-card-footer text-center py-3">
                    <a href="{{ url('/') }}" class="login-link d-inline-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i>
                        <span>{{ __('Back to Welcome Page') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .login-brand-title {
        font-size: clamp(2rem, 5vw, 2.75rem);
        font-weight: 700;
        letter-spacing: 0.12em;
        line-height: 1;
        color: var(--app-text);
        margin-bottom: 0.5rem;
        text-shadow: 0 0 24px rgba(99, 102, 241, 0.35);
    }

    .login-brand-subtitle {
        font-family: var(--font-mono);
        font-size: 0.78rem;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--app-text-muted);
    }

    .login-card {
        background: rgba(26, 29, 32, 0.82) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--app-border) !important;
        border-radius: 6px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), 0 0 35px rgba(99, 102, 241, 0.08);
        overflow: hidden;
    }

    .login-card-header {
        background: rgba(255, 255, 255, 0.02) !important;
        border-bottom: 1px solid var(--app-border) !important;
        font-family: var(--font-mono);
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        padding: 0.9rem 1.25rem;
        color: var(--app-text);
    }

    .login-system-badge {
        font-family: var(--font-mono);
        font-size: 0.68rem;
        letter-spacing: 0.06em;
        padding: 0.3em 0.6em;
        background-color: rgba(99, 102, 241, 0.15);
        color: #a5b4fc;
        border: 1px solid rgba(99, 102, 241, 0.35);
        border-radius: 3px;
        font-weight: 500;
    }

    .login-form-label {
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: var(--app-text-muted);
        text-transform: uppercase;
        font-family: var(--font-mono);
    }

    .login-input-icon {
        background-color: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--app-border);
        border-right: none;
        color: var(--app-text-muted);
        font-size: 0.95rem;
        border-radius: 4px 0 0 4px !important;
    }

    .login-input {
        background-color: rgba(255, 255, 255, 0.03) !important;
        border: 1px solid var(--app-border) !important;
        color: var(--app-text) !important;
        font-size: 0.92rem;
        padding: 0.65rem 0.85rem;
        border-radius: 0 4px 4px 0 !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
    }

    .login-input::placeholder {
        color: rgba(148, 163, 184, 0.4);
    }

    .login-input:focus {
        background-color: rgba(255, 255, 255, 0.06) !important;
        border-color: var(--app-primary) !important;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25) !important;
        color: #ffffff !important;
    }

    .login-checkbox {
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--app-border);
        cursor: pointer;
    }

    .login-checkbox:checked {
        background-color: var(--app-primary);
        border-color: var(--app-primary);
    }

    .login-checkbox:focus {
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        border-color: var(--app-primary);
    }

    .login-checkbox-label {
        font-size: 0.86rem;
        color: var(--app-text-muted);
        cursor: pointer;
        user-select: none;
    }

    .btn-login {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        padding: 0.72rem 1.5rem;
        background-color: var(--app-primary);
        color: #ffffff;
        border: 1px solid var(--app-primary);
        border-radius: 4px;
        font-family: var(--font-mono);
        font-size: 0.92rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        transition: all 0.25s ease;
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.3);
    }

    .btn-login:hover {
        background-color: var(--app-primary-hover);
        border-color: var(--app-primary-hover);
        color: #ffffff;
        box-shadow: 0 0 24px rgba(99, 102, 241, 0.5);
        transform: translateY(-1px);
    }

    .btn-login:focus-visible {
        outline: 2px solid #ffffff;
        outline-offset: 2px;
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .login-card-footer {
        background: rgba(255, 255, 255, 0.015) !important;
        border-top: 1px solid var(--app-border) !important;
    }

    .login-link {
        color: var(--app-text-muted);
        text-decoration: none;
        font-family: var(--font-mono);
        font-size: 0.82rem;
        letter-spacing: 0.04em;
        transition: color 0.15s ease;
    }

    .login-link:hover {
        color: var(--app-primary);
    }
</style>
@endsection
