@extends('layouts.pos')

@section('title', __('messages.settings.title'))
@section('page-icon', 'settings')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-1">{{ __('messages.settings.title') }}</h5>
                    <p class="text-muted mb-0">{{ __('messages.settings.subtitle') }}</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label required">{{ __('messages.settings.shop_name') }}</label>
                                <input type="text" id="name" name="name"
                                       value="{{ old('name', $settings->name ?: config('pos.shop.name')) }}"
                                       class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label required">{{ __('messages.settings.shop_phone') }}</label>
                                <input type="text" id="phone" name="phone"
                                       value="{{ old('phone', $settings->phone ?: config('pos.shop.phone')) }}"
                                       class="form-control @error('phone') is-invalid @enderror" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label required">{{ __('messages.settings.shop_address') }}</label>
                                <input type="text" id="address" name="address"
                                       value="{{ old('address', $settings->address ?: config('pos.shop.address')) }}"
                                       class="form-control @error('address') is-invalid @enderror" required>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="logo" class="form-label">{{ __('messages.settings.logo') }}</label>
                                <input type="file" id="logo" name="logo" accept="image/*"
                                       class="form-control @error('logo') is-invalid @enderror">
                                <small class="text-muted">{{ __('messages.settings.logo_hint') }}</small>
                                @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            @if ($settings->hasMedia('logo'))
                                <div class="col-md-6">
                                    <label class="form-label d-block">{{ __('messages.settings.current_logo') }}</label>
                                    <img src="{{ $settings->getFirstMediaUrl('logo', 'thumb') }}" alt="{{ $settings->displayName() }}"
                                         class="rounded border bg-white p-1 zoomable" style="max-height: 120px;"
                                         data-lightbox-src="{{ $settings->getFirstMediaUrl('logo') }}"
                                         data-lightbox-caption="{{ $settings->displayName() }}"
                                         title="{{ __('messages.products.click_to_zoom') }}">
                                </div>
                            @endif
                        </div>

                        <p class="text-muted small mt-3 mb-0">{{ __('messages.settings.required_hint') }}</p>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ __('messages.actions.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
