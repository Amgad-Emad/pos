{{-- شاشة البيع (POS): بحث مباشر + سلة + ملخص. يتطلب $paymentMethods، و$sale اختياريًا للتعديل --}}
@php($sale = $sale ?? null)
@php(
    $initialCart = old('items')
        ? collect(old('items'))->map(fn ($item) => [
            'product_id' => (int) $item['product_id'],
            'qty' => (int) $item['qty'],
            'name' => \App\Models\Product::find($item['product_id'])?->name ?? '',
            'code' => \App\Models\Product::find($item['product_id'])?->code ?? '',
            'price' => (float) (\App\Models\Product::find($item['product_id'])?->selling_price ?? 0),
            'available' => (int) (\App\Models\Product::find($item['product_id'])?->quantity ?? 0),
        ])->values()->all()
        : ($sale?->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'qty' => $item->qty,
            'name' => $item->product->name,
            'code' => $item->product->code,
            'price' => (float) $item->product->selling_price,
            // عند التعديل تُعاد الكميات أولًا، فالمتاح = مخزون حالي + كمية الفاتورة
            'available' => $item->product->quantity + $item->qty,
        ])->values()->all() ?? [])
)

<div x-data="posForm({
        cart: @js($initialCart),
        discount: {{ (float) old('sale_amount', $sale?->sale_amount ?? 0) }},
        paid: {{ (float) old('paid_amount', $sale?->paid_amount ?? 0) }},
        searchUrl: @js(route('sales.search-products')),
        insufficientMessage: @js(__('messages.sales.available')),
     })">

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">

                    <div class="position-relative mb-3">
                        <input type="search" class="form-control form-control-lg" x-model="searchTerm"
                               @input.debounce.300ms="search()" @keydown.escape="results = []"
                               placeholder="{{ __('messages.sales.search_product') }}" autocomplete="off">

                        <div class="card position-absolute w-100 shadow mt-1" style="z-index: 1050;"
                             x-show="results.length > 0 || (searched && searchTerm)" x-cloak @click.outside="results = []; searched = false">
                            <ul class="list-group list-group-flush">
                                <template x-for="product in results" :key="product.id">
                                    <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                        style="cursor:pointer;" @click="addProduct(product)">
                                        <span>
                                            <span class="fw-medium" x-text="product.name"></span>
                                            <span class="badge bg-light text-dark border" x-text="product.code"></span>
                                        </span>
                                        <span class="text-muted small">
                                            <span x-text="format(product.selling_price)"></span> {{ __('messages.currency') }}
                                            — {{ __('messages.sales.available') }}: <span x-text="product.quantity"></span>
                                        </span>
                                    </li>
                                </template>
                                <li class="list-group-item text-muted text-center" x-show="searched && searchTerm && results.length === 0">
                                    {{ __('messages.sales.no_search_results') }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <h5 class="mb-2">{{ __('messages.sales.cart') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th>{{ __('messages.fields.name') }}</th>
                                    <th>{{ __('messages.fields.code') }}</th>
                                    <th>{{ __('messages.fields.price') }}</th>
                                    <th style="width:140px;">{{ __('messages.fields.quantity') }}</th>
                                    <th>{{ __('messages.fields.total') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(line, index) in cart" :key="line.product_id">
                                    <tr>
                                        <td>
                                            <span class="fw-medium" x-text="line.name"></span>
                                            <input type="hidden" :name="`items[${index}][product_id]`" :value="line.product_id">
                                            <input type="hidden" :name="`items[${index}][qty]`" :value="line.qty">
                                        </td>
                                        <td><span class="badge bg-light text-dark border" x-text="line.code"></span></td>
                                        <td x-text="format(line.price)"></td>
                                        <td>
                                            <div class="qty-stepper">
                                                <button type="button" class="btn" aria-label="{{ __('messages.actions.decrease') }}" @click="decrement(line)">−</button>
                                                <input type="number" class="form-control form-control-sm" min="1" :max="line.available"
                                                       x-model.number="line.qty" @change="clampQty(line)">
                                                <button type="button" class="btn" aria-label="{{ __('messages.actions.increase') }}" @click="increment(line)">+</button>
                                            </div>
                                            <small class="text-muted">{{ __('messages.sales.available') }}: <span x-text="line.available"></span></small>
                                        </td>
                                        <td class="fw-semibold" x-text="format(line.price * line.qty)"></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-soft-danger btn-icon" @click="remove(index)">✕</button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="cart.length === 0">
                                    <td colspan="6" class="text-center text-muted py-4">{{ __('messages.sales.cart_empty') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.sales.summary') }}</h5>
                </div>
                <div class="card-body d-flex flex-column gap-3">

                    <div>
                        <label class="form-label mb-1 required">{{ __('messages.fields.date') }}</label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date', $sale?->date?->format('Y-m-d') ?? now()->toDateString()) }}" required>
                        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <fieldset class="border rounded p-2">
                        <legend class="fs-6 text-muted mb-1 float-none w-auto px-1">{{ __('messages.sales.client_data') }}</legend>
                        <div class="mb-2">
                            <input type="text" name="client_name" class="form-control form-control-sm @error('client_name') is-invalid @enderror"
                                   value="{{ old('client_name', $sale?->client_name) }}" placeholder="{{ __('messages.fields.client_name') }}">
                            @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <input type="text" name="client_phone" class="form-control form-control-sm @error('client_phone') is-invalid @enderror"
                                   value="{{ old('client_phone', $sale?->client_phone) }}" placeholder="{{ __('messages.fields.client_phone') }}">
                            @error('client_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </fieldset>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">{{ __('messages.fields.total_amount') }}</span>
                        <span class="fw-semibold"><span x-text="format(total)"></span> {{ __('messages.currency') }}</span>
                    </div>

                    <div>
                        <label class="form-label mb-1">{{ __('messages.fields.sale_amount') }}</label>
                        <input type="number" step="0.01" min="0" name="sale_amount" x-model.number="discount"
                               class="form-control @error('sale_amount') is-invalid @enderror">
                        @error('sale_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between border-top pt-2">
                        <span class="text-muted">{{ __('messages.fields.total_after_sale') }}</span>
                        <span class="fw-bold fs-5"><span x-text="format(totalAfter)"></span> {{ __('messages.currency') }}</span>
                    </div>

                    <div>
                        <label class="form-label mb-1 required">{{ __('messages.fields.paid_amount') }}</label>
                        <input type="number" step="0.01" min="0" name="paid_amount" x-model.number="paid"
                               class="form-control @error('paid_amount') is-invalid @enderror" required>
                        @error('paid_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">{{ __('messages.fields.remaining_amount') }}</span>
                        <span class="fw-semibold" :class="remaining > 0 ? 'text-danger' : 'text-success'">
                            <span x-text="format(remaining)"></span> {{ __('messages.currency') }}
                        </span>
                    </div>

                    <div>
                        <label class="form-label mb-1 required">{{ __('messages.fields.payment_method') }}</label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            @foreach ($paymentMethods as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_method', $sale?->payment_method?->value ?? 'cash') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" :disabled="cart.length === 0">
                        {{ __('messages.sales.save_and_print') }}
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn btn-light">{{ __('messages.actions.back') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>[x-cloak]{display:none!important;}</style>
@endpush

@push('scripts')
    <script>
        function posForm(config) {
            return {
                searchTerm: '',
                results: [],
                searched: false,
                cart: config.cart || [],
                discount: config.discount || 0,
                paid: config.paid || 0,

                async search() {
                    const term = this.searchTerm.trim();
                    if (!term) {
                        this.results = [];
                        this.searched = false;
                        return;
                    }
                    try {
                        const response = await fetch(`${config.searchUrl}?q=${encodeURIComponent(term)}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        this.results = await response.json();
                        this.searched = true;
                    } catch (error) {
                        this.results = [];
                        this.searched = true;
                    }
                },
                addProduct(product) {
                    const line = this.cart.find(item => item.product_id === product.id);
                    if (line) {
                        if (line.qty < line.available) line.qty++;
                    } else {
                        this.cart.push({
                            product_id: product.id,
                            name: product.name,
                            code: product.code,
                            price: Number(product.selling_price),
                            available: Number(product.quantity),
                            qty: 1,
                        });
                    }
                    this.results = [];
                    this.searched = false;
                    this.searchTerm = '';
                },
                increment(line) {
                    if (line.qty < line.available) line.qty++;
                },
                decrement(line) {
                    if (line.qty > 1) line.qty--;
                },
                clampQty(line) {
                    let qty = Math.floor(Number(line.qty) || 1);
                    if (qty < 1) qty = 1;
                    if (qty > line.available) qty = line.available;
                    line.qty = qty;
                },
                remove(index) {
                    this.cart.splice(index, 1);
                },
                get total() {
                    return this.cart.reduce((sum, line) => sum + line.price * line.qty, 0);
                },
                get totalAfter() {
                    return Math.max(this.total - (Number(this.discount) || 0), 0);
                },
                get remaining() {
                    return this.totalAfter - (Number(this.paid) || 0);
                },
                format(value) {
                    return (Math.round(value * 100) / 100).toFixed(2);
                },
            };
        }
    </script>
@endpush
