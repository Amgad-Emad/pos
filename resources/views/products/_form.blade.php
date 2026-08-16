{{-- حقول نموذج المنتج المشتركة بين الإضافة والتعديل --}}
@php($product = $product ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label required">{{ __('messages.fields.name') }}</label>
        <input type="text" id="name" name="name" value="{{ old('name', $product?->name) }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="code" class="form-label required">{{ __('messages.fields.code') }}</label>
        <input type="text" id="code" name="code" value="{{ old('code', $product?->code) }}"
               class="form-control @error('code') is-invalid @enderror" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="category_id" class="form-label required">{{ __('messages.fields.category') }}</label>
        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">{{ __('messages.actions.choose') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
                @foreach ($category->children as $child)
                    <option value="{{ $child->id }}" @selected(old('category_id', $product?->category_id) == $child->id)>
                        &nbsp;&nbsp;— {{ $child->name }}
                    </option>
                @endforeach
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="supplier_id" class="form-label required">{{ __('messages.fields.supplier') }}</label>
        <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
            <option value="">{{ __('messages.actions.choose') }}</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $product?->supplier_id) == $supplier->id)>
                    {{ $supplier->name }}
                </option>
            @endforeach
        </select>
        @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label for="purchase_price" class="form-label required">{{ __('messages.fields.purchase_price') }}</label>
        <input type="number" step="0.01" min="0" id="purchase_price" name="purchase_price"
               value="{{ old('purchase_price', $product?->purchase_price) }}"
               class="form-control @error('purchase_price') is-invalid @enderror" required>
        @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label for="selling_price" class="form-label required">{{ __('messages.fields.selling_price') }}</label>
        <input type="number" step="0.01" min="0" id="selling_price" name="selling_price"
               value="{{ old('selling_price', $product?->selling_price) }}"
               class="form-control @error('selling_price') is-invalid @enderror" required>
        @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label for="wholesale_price" class="form-label required">{{ __('messages.fields.wholesale_price') }}</label>
        <input type="number" step="0.01" min="0" id="wholesale_price" name="wholesale_price"
               value="{{ old('wholesale_price', $product?->wholesale_price) }}"
               class="form-control @error('wholesale_price') is-invalid @enderror" required>
        @error('wholesale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label for="quantity" class="form-label required">{{ __('messages.fields.quantity') }}</label>
        <input type="number" min="0" id="quantity" name="quantity"
               value="{{ old('quantity', $product?->quantity ?? 0) }}"
               class="form-control @error('quantity') is-invalid @enderror" required>
        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="image" class="form-label">{{ __('messages.products.image_hint') }}</label>
        <input type="file" id="image" name="image" accept="image/*"
               class="form-control @error('image') is-invalid @enderror">
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if ($product?->hasMedia('image'))
        <div class="col-md-6">
            <label class="form-label d-block">{{ __('messages.products.current_image') }}</label>
            <img src="{{ $product->getFirstMediaUrl('image', 'thumb') }}" alt="{{ $product->name }}" class="rounded border" width="64" height="64">
        </div>
    @endif
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.actions.save') }}</button>
    <a href="{{ route('products.index') }}" class="btn btn-light">{{ __('messages.actions.back') }}</a>
</div>
