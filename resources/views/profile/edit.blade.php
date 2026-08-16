@extends('layouts.pos')

@section('title', __('messages.profile.title'))
@section('page-icon', 'key-round')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-1">{{ __('messages.profile.title') }}</h5>
                    <p class="text-muted mb-0">{{ __('messages.profile.subtitle') }}</p>
                </div>
                <div class="card-body">

                    @if (session('status') === 'password-updated')
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ __('messages.profile.password_updated') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.actions.close') }}"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label required">{{ __('messages.fields.current_password') }}</label>
                            <input type="password" id="current_password" name="current_password"
                                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="current-password" required>
                            @error('current_password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label required">{{ __('messages.fields.new_password') }}</label>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password" required>
                            @error('password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label required">{{ __('messages.fields.password_confirmation') }}</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control" autocomplete="new-password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('messages.actions.save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
