{{-- نموذج بحث موحد: يقبل $placeholder اختياريًا --}}
<form method="GET" class="d-flex gap-2" role="search">
    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
           style="max-width: 260px;" placeholder="{{ $placeholder ?? __('messages.actions.search') }}">
    <button type="submit" class="btn btn-sm btn-soft-primary">{{ __('messages.actions.search') }}</button>
    @if (request('q'))
        <a href="{{ url()->current() }}" class="btn btn-sm btn-soft-secondary">{{ __('messages.actions.reset') }}</a>
    @endif
</form>
