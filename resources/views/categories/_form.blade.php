@php($category = $category ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label required">{{ __('messages.fields.name') }}</label>
        <input type="text" id="name" name="name" value="{{ old('name', $category?->name) }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="parent_id" class="form-label">{{ __('messages.fields.parent_category') }}</label>
        <select id="parent_id" name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
            <option value="">{{ __('messages.categories.none_parent') }}</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $category?->parent_id) == $parent->id)>
                    {{ $parent->name }}
                </option>
            @endforeach
        </select>
        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.actions.save') }}</button>
    <a href="{{ route('categories.index') }}" class="btn btn-light">{{ __('messages.actions.back') }}</a>
</div>
