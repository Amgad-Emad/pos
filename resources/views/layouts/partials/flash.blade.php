@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.actions.close') }}"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.actions.close') }}"></button>
    </div>
@endif

@foreach (['sale', 'return'] as $errorKey)
    @if ($errors->has($errorKey))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first($errorKey) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.actions.close') }}"></button>
        </div>
    @endif
@endforeach

@if ($errors->has('items') || $errors->has('items.*'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0 ps-3">
            @foreach ($errors->get('items') as $message)
                <li>{{ $message }}</li>
            @endforeach
            @foreach ($errors->keys() as $key)
                @if (str_starts_with($key, 'items.'))
                    @foreach ($errors->get($key) as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                @endif
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.actions.close') }}"></button>
    </div>
@endif
