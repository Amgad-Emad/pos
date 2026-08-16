@php($user = $user ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label required">{{ __('messages.fields.name') }}</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user?->name) }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label required">{{ __('messages.fields.email') }}</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user?->email) }}"
               class="form-control @error('email') is-invalid @enderror" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label {{ $user ? '' : 'required' }}">{{ __('messages.fields.password') }}</label>
        <input type="password" id="password" name="password" autocomplete="new-password"
               class="form-control @error('password') is-invalid @enderror" @if(!$user) required @endif>
        @if ($user)
            <small class="text-muted">{{ __('messages.users.password_hint') }}</small>
        @endif
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label {{ $user ? '' : 'required' }}">{{ __('messages.fields.password_confirmation') }}</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
               class="form-control" @if(!$user) required @endif>
    </div>

    @if (! $user?->isAdmin())
        <div class="col-md-6">
            <div class="form-check form-switch mt-2">
                <input type="hidden" name="active" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1"
                       @checked(old('active', $user?->active ?? true))>
                <label class="form-check-label" for="active">{{ __('messages.fields.active') }}</label>
            </div>
        </div>
    @endif
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.actions.save') }}</button>
    <a href="{{ route('users.index') }}" class="btn btn-light">{{ __('messages.actions.back') }}</a>
</div>
