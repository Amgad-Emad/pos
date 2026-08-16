@extends('layouts.pos')

@section('title', __('messages.returns.create'))
@section('page-icon', 'undo-2')

@section('content')
    <form method="POST" action="{{ route('returns.store') }}"
          x-data="returnForm({
              searchUrl: @js(route('returns.search-sale')),
              initialInvoice: @js(old('invoice_number', $invoiceNumber)),
              notFoundMessage: @js(__('messages.returns.invoice_not_found')),
          })">
        @csrf

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">

                        <label class="form-label mb-1">{{ __('messages.fields.invoice_number') }}</label>
                        <div class="d-flex gap-2 mb-1">
                            <input type="search" class="form-control form-control-lg" x-model="invoiceTerm"
                                   @keydown.enter.prevent="lookup()" dir="ltr" style="text-align: end;"
                                   placeholder="{{ __('messages.returns.search_invoice') }}" autocomplete="off">
                            <button type="button" class="btn btn-primary px-3 d-inline-flex align-items-center gap-1" @click="lookup()" :disabled="loading">
                                <i data-lucide="search" style="width:16px;height:16px;"></i>
                                {{ __('messages.actions.search') }}
                            </button>
                        </div>
                        <small class="text-muted d-block mb-3">{{ __('messages.returns.search_hint') }}</small>

                        <div class="alert alert-warning mb-3" x-show="notFound" x-cloak>
                            {{ __('messages.returns.invoice_not_found') }}
                        </div>

                        @error('sale_id')
                            <div class="alert alert-danger mb-3">{{ $message }}</div>
                        @enderror
                        @error('items')
                            <div class="alert alert-danger mb-3">{{ $message }}</div>
                        @enderror

                        <template x-if="sale">
                            <div>
                                <input type="hidden" name="sale_id" :value="sale.id">

                                <fieldset class="border rounded p-2 mb-3">
                                    <legend class="fs-6 text-muted mb-1 float-none w-auto px-1">{{ __('messages.returns.invoice_data') }}</legend>
                                    <div class="d-flex flex-wrap gap-3 small">
                                        <span>{{ __('messages.fields.invoice_number') }}: <strong dir="ltr" x-text="sale.invoice_number"></strong></span>
                                        <span>{{ __('messages.fields.date') }}: <span x-text="sale.date"></span></span>
                                        <span>{{ __('messages.fields.client_name') }}: <span x-text="sale.client_name || @js(__('messages.invoices.cash_client'))"></span></span>
                                        <span>{{ __('messages.fields.total_after_sale') }}:
                                            <strong x-text="format(sale.total_after_sale)"></strong> {{ __('messages.currency') }}
                                        </span>
                                        <span class="text-danger" x-show="sale.sale_amount > 0">
                                            {{ __('messages.fields.sale_amount') }}: <span x-text="format(sale.sale_amount)"></span> {{ __('messages.currency') }}
                                        </span>
                                    </div>
                                </fieldset>

                                <h5 class="mb-2">{{ __('messages.returns.items') }}</h5>
                                <div class="alert alert-info py-2 small" x-show="sale.sale_amount > 0">
                                    <i data-lucide="info" style="width:14px;height:14px;"></i>
                                    {{ __('messages.returns.refund_note') }}
                                </div>

                                <div class="alert alert-warning" x-show="allReturned">
                                    {{ __('messages.returns.nothing_returnable') }}
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="bg-light-subtle">
                                            <tr>
                                                <th>{{ __('messages.fields.name') }}</th>
                                                <th>{{ __('messages.fields.code') }}</th>
                                                <th>{{ __('messages.fields.sold_qty') }}</th>
                                                <th>{{ __('messages.fields.unit_refund') }}</th>
                                                <th style="width:150px;">{{ __('messages.fields.return_qty') }}</th>
                                                <th>{{ __('messages.fields.total_refund') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(line, index) in lines" :key="line.sale_item_id">
                                                <tr :class="line.returnable === 0 ? 'table-light text-muted' : ''">
                                                    <td>
                                                        <span class="fw-medium" x-text="line.name"></span>
                                                        <template x-if="line.qty > 0">
                                                            <span>
                                                                <input type="hidden" :name="`items[${index}][sale_item_id]`" :value="line.sale_item_id">
                                                                <input type="hidden" :name="`items[${index}][qty]`" :value="line.qty">
                                                            </span>
                                                        </template>
                                                    </td>
                                                    <td><span class="badge bg-light text-dark border" x-text="line.code"></span></td>
                                                    <td>
                                                        <span x-text="line.sold_qty"></span>
                                                        <small class="text-muted d-block" x-show="line.returned_before > 0">
                                                            {{ __('messages.returns.returned_before') }}: <span x-text="line.returned_before"></span>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span x-text="format(line.unit_refund)"></span>
                                                        <small class="text-muted text-decoration-line-through d-block" x-show="line.unit_refund < line.price"
                                                               x-text="format(line.price)"></small>
                                                    </td>
                                                    <td>
                                                        <template x-if="line.returnable > 0">
                                                            <div>
                                                                <div class="qty-stepper">
                                                                    <button type="button" class="btn" aria-label="{{ __('messages.actions.decrease') }}" @click="decrement(line)">−</button>
                                                                    <input type="number" class="form-control form-control-sm" min="0" :max="line.returnable"
                                                                           x-model.number="line.qty" @change="clampQty(line)">
                                                                    <button type="button" class="btn" aria-label="{{ __('messages.actions.increase') }}" @click="increment(line)">+</button>
                                                                </div>
                                                                <small class="text-muted">{{ __('messages.fields.returnable_qty') }}: <span x-text="line.returnable"></span></small>
                                                            </div>
                                                        </template>
                                                        <template x-if="line.returnable === 0">
                                                            <span class="badge badge-soft-secondary">{{ __('messages.returns.fully_returned') }}</span>
                                                        </template>
                                                    </td>
                                                    <td class="fw-semibold" x-text="format(line.unit_refund * line.qty)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.returns.summary') }}</h5>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">

                        <div>
                            <label class="form-label mb-1 required">{{ __('messages.fields.date') }}</label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date', now()->toDateString()) }}" required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="form-label mb-1">{{ __('messages.fields.notes') }}</label>
                            <input type="text" name="notes" maxlength="255" class="form-control @error('notes') is-invalid @enderror"
                                   value="{{ old('notes') }}">
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="text-muted">{{ __('messages.fields.total_refund') }}</span>
                            <span class="fw-bold fs-5 text-danger">
                                <span x-text="format(totalRefund)"></span> {{ __('messages.currency') }}
                            </span>
                        </div>

                        <small class="text-muted" x-show="sale && !hasSelection">{{ __('messages.returns.no_items_selected') }}</small>

                        <button type="submit" class="btn btn-primary btn-lg" :disabled="!hasSelection">
                            {{ __('messages.returns.save') }}
                        </button>
                        <a href="{{ route('returns.index') }}" class="btn btn-light">{{ __('messages.actions.back') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
    <style>[x-cloak]{display:none!important;}</style>
@endpush

@push('scripts')
    <script>
        function returnForm(config) {
            return {
                invoiceTerm: config.initialInvoice || '',
                loading: false,
                notFound: false,
                sale: null,
                lines: [],

                init() {
                    if (this.invoiceTerm) this.lookup();
                },
                async lookup() {
                    const term = this.invoiceTerm.trim();
                    if (!term) return;
                    this.loading = true;
                    this.notFound = false;
                    try {
                        const response = await fetch(`${config.searchUrl}?q=${encodeURIComponent(term)}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await response.json();
                        if (!data.found) {
                            this.sale = null;
                            this.lines = [];
                            this.notFound = true;
                        } else {
                            this.sale = data.sale;
                            this.lines = data.sale.items.map(item => ({
                                sale_item_id: item.sale_item_id,
                                name: item.name,
                                code: item.code,
                                sold_qty: item.qty,
                                returned_before: item.qty - item.returnable,
                                returnable: item.returnable,
                                price: Number(item.price),
                                unit_refund: Number(item.unit_refund),
                                qty: 0,
                            }));
                        }
                    } catch (error) {
                        this.sale = null;
                        this.lines = [];
                        this.notFound = true;
                    } finally {
                        this.loading = false;
                    }
                },
                increment(line) {
                    if (line.qty < line.returnable) line.qty++;
                },
                decrement(line) {
                    if (line.qty > 0) line.qty--;
                },
                clampQty(line) {
                    let qty = Math.floor(Number(line.qty) || 0);
                    if (qty < 0) qty = 0;
                    if (qty > line.returnable) qty = line.returnable;
                    line.qty = qty;
                },
                get allReturned() {
                    return this.lines.length > 0 && this.lines.every(line => line.returnable === 0);
                },
                get hasSelection() {
                    return this.lines.some(line => line.qty > 0);
                },
                get totalRefund() {
                    return this.lines.reduce((sum, line) => sum + line.unit_refund * line.qty, 0);
                },
                format(value) {
                    return (Math.round(value * 100) / 100).toFixed(2);
                },
            };
        }
    </script>
@endpush
