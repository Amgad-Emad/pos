@php($supplier = $supplier ?? null)

<div class="row g-3">
    <div class="col-md-4">
        <label for="name" class="form-label required">{{ __('messages.fields.name') }}</label>
        <input type="text" id="name" name="name" value="{{ old('name', $supplier?->name) }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="phone" class="form-label required">{{ __('messages.fields.phone') }}</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $supplier?->phone) }}"
               class="form-control @error('phone') is-invalid @enderror" required>
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="address" class="form-label">{{ __('messages.fields.address') }}</label>
        <input type="text" id="address" name="address" value="{{ old('address', $supplier?->address) }}"
               class="form-control @error('address') is-invalid @enderror">
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.actions.save') }}</button>
    <a href="{{ route('suppliers.index') }}" class="btn btn-light">{{ __('messages.actions.back') }}</a>
</div>
