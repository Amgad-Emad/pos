{{-- نموذج فاتورة الشراء مع مكرر الأصناف (Alpine.js). يتطلب $suppliers و$categories، و$purchase اختياريًا --}}
@php($purchase = $purchase ?? null)
@php(
    $initialItems = old('items')
        ? collect(old('items'))->map(fn ($item) => collect($item)->except('image')->all())->values()->all()
        : ($purchase?->items->map(fn ($item) => [
            'name' => $item->name,
            'code' => $item->code,
            'main_category_id' => $item->category?->parent_id ?: $item->category_id,
            'category_id' => $item->category_id,
            'purchase_price' => (float) $item->purchase_price,
            'selling_price' => (float) $item->selling_price,
            'wholesale_price' => (float) $item->wholesale_price,
            'quantity' => $item->quantity,
        ])->values()->all() ?? [])
)
@php(
    $categoryTree = $categories->map(fn ($category) => [
        'id' => $category->id,
        'name' => $category->name,
        'children' => $category->children->map(fn ($child) => [
            'id' => $child->id,
            'name' => $child->name,
        ])->values()->all(),
    ])->values()->all()
)

<div x-data="purchaseForm(@js(array_values($initialItems)), @js((float) old('amount_paid', $purchase?->amount_paid ?? 0)), @js($categoryTree))">

    <div class="row g-3">
        <div class="col-md-4">
            <label for="date" class="form-label required">{{ __('messages.fields.date') }}</label>
            <input type="date" id="date" name="date"
                   value="{{ old('date', $purchase?->date?->format('Y-m-d') ?? now()->toDateString()) }}"
                   class="form-control @error('date') is-invalid @enderror" required>
            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label for="supplier_id" class="form-label required">{{ __('messages.fields.supplier') }}</label>
            <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                <option value="">{{ __('messages.actions.choose') }}</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchase?->supplier_id) == $supplier->id)>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label for="sale_price" class="form-label">{{ __('messages.fields.sale_amount') }}</label>
            <input type="number" step="0.01" min="0" id="sale_price" name="sale_price"
                   value="{{ old('sale_price', $purchase?->sale_price) }}"
                   class="form-control @error('sale_price') is-invalid @enderror">
            @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0">{{ __('messages.purchases.items') }}</h5>
        <button type="button" class="btn btn-soft-success btn-sm" @click="addItem()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            {{ __('messages.purchases.add_item') }}
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-2">
            <thead class="bg-light-subtle">
                <tr>
                    <th style="min-width:160px;">{{ __('messages.fields.name') }}</th>
                    <th style="min-width:140px;">{{ __('messages.fields.code') }}</th>
                    <th style="min-width:140px;">{{ __('messages.fields.parent_category') }}</th>
                    <th style="min-width:140px;">{{ __('messages.fields.sub_category') }}</th>
                    <th style="min-width:150px;">{{ __('messages.fields.image') }}</th>
                    <th style="min-width:110px;">{{ __('messages.fields.purchase_price') }}</th>
                    <th style="min-width:110px;">{{ __('messages.fields.selling_price') }}</th>
                    <th style="min-width:110px;">{{ __('messages.fields.wholesale_price') }}</th>
                    <th style="min-width:90px;">{{ __('messages.fields.quantity') }}</th>
                    <th style="min-width:110px;">{{ __('messages.fields.total') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in items" :key="index">
                    <tr>
                        <td>
                            <input type="text" class="form-control form-control-sm" x-model="item.name"
                                   :name="`items[${index}][name]`" required>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" x-model="item.code"
                                   :name="`items[${index}][code]`"
                                   placeholder="{{ __('messages.products.code_hint') }}">
                        </td>
                        <td>
                            <select class="form-select form-select-sm" x-model="item.main_category_id"
                                    :name="`items[${index}][main_category_id]`" @change="onMainCategoryChange(item)" required>
                                <option value="">{{ __('messages.actions.choose') }}</option>
                                <template x-for="category in categories" :key="category.id">
                                    <option :value="category.id" x-text="category.name"
                                            :selected="String(item.main_category_id) === String(category.id)"></option>
                                </template>
                            </select>
                        </td>
                        <td>
                            <select class="form-select form-select-sm" x-model="item.category_id"
                                    :name="`items[${index}][category_id]`" :disabled="!item.main_category_id" required>
                                <template x-for="option in subOptions(item)" :key="option.id">
                                    <option :value="option.id" x-text="option.name"
                                            :selected="String(item.category_id) === String(option.id)"></option>
                                </template>
                            </select>
                        </td>
                        <td>
                            <input type="file" class="form-control form-control-sm" accept="image/*"
                                   :name="`items[${index}][image]`">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                   x-model.number="item.purchase_price" :name="`items[${index}][purchase_price]`" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                   x-model.number="item.selling_price" :name="`items[${index}][selling_price]`" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                   x-model.number="item.wholesale_price" :name="`items[${index}][wholesale_price]`" required>
                        </td>
                        <td>
                            <input type="number" min="1" class="form-control form-control-sm"
                                   x-model.number="item.quantity" :name="`items[${index}][quantity]`" required>
                        </td>
                        <td class="text-nowrap fw-semibold" x-text="lineTotal(item)"></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-soft-danger btn-icon" @click="removeItem(index)"
                                    title="{{ __('messages.purchases.remove_item') }}">
                                <i data-lucide="x" style="width:14px;height:14px;"></i>
                            </button>
                        </td>
                    </tr>
                </template>
                <tr x-show="items.length === 0">
                    <td colspan="11" class="text-center text-muted py-3">{{ __('messages.errors.items_required') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <label class="form-label">{{ __('messages.purchases.items_total') }}</label>
            <input type="text" class="form-control bg-body-secondary" :value="formatted(total)" readonly tabindex="-1">
        </div>
        <div class="col-md-4">
            <label for="amount_paid" class="form-label required">{{ __('messages.fields.amount_paid') }}</label>
            <input type="number" step="0.01" min="0" id="amount_paid" name="amount_paid" x-model.number="amountPaid"
                   class="form-control @error('amount_paid') is-invalid @enderror" required>
            @error('amount_paid')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('messages.fields.remaining_amount') }}</label>
            <input type="text" class="form-control bg-body-secondary" :value="formatted(remaining)" readonly tabindex="-1">
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary" :disabled="items.length === 0">{{ __('messages.actions.save') }}</button>
        <a href="{{ route('purchases.index') }}" class="btn btn-light">{{ __('messages.actions.back') }}</a>
    </div>
</div>

@push('scripts')
    <script>
        function purchaseForm(initialItems, initialPaid, categories) {
            return {
                items: initialItems.length ? initialItems : [purchaseEmptyItem()],
                amountPaid: initialPaid || 0,
                categories: categories || [],

                init() {
                    this.$nextTick(() => window.lucide?.createIcons());
                },
                addItem() {
                    this.items.push(purchaseEmptyItem());
                    this.$nextTick(() => window.lucide?.createIcons());
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                // خيارات التصنيف الفرعي بناءً على التصنيف الرئيسي المختار
                subOptions(item) {
                    const main = this.categories.find(
                        category => String(category.id) === String(item.main_category_id)
                    );
                    if (!main) return [];

                    return [{ id: main.id, name: @js(__('messages.purchases.no_sub_category')) }]
                        .concat(main.children);
                },
                onMainCategoryChange(item) {
                    // افتراضيًا يُسجل الصنف على التصنيف الرئيسي حتى يُختار فرعي
                    item.category_id = item.main_category_id || '';
                },
                lineTotal(item) {
                    return this.formatted((Number(item.purchase_price) || 0) * (Number(item.quantity) || 0));
                },
                get total() {
                    return this.items.reduce(
                        (sum, item) => sum + (Number(item.purchase_price) || 0) * (Number(item.quantity) || 0),
                        0
                    );
                },
                get remaining() {
                    return this.total - (Number(this.amountPaid) || 0);
                },
                formatted(value) {
                    return (Math.round(value * 100) / 100).toFixed(2);
                },
            };
        }

        function purchaseEmptyItem() {
            return {
                name: '',
                code: '',
                main_category_id: '',
                category_id: '',
                purchase_price: null,
                selling_price: null,
                wholesale_price: null,
                quantity: 1,
            };
        }
    </script>
@endpush
