<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.invoices.invoice') }} {{ $sale->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Cairo", "Tajawal", "Segoe UI", Tahoma, Arial, sans-serif;
            background: #f1f3f5;
            color: #212529;
            direction: rtl;
        }
        .toolbar {
            display: flex;
            gap: 8px;
            justify-content: center;
            padding: 14px;
        }
        .toolbar a, .toolbar button {
            border: 1px solid #ced4da;
            background: #fff;
            color: #212529;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
        }
        .toolbar .primary { background: #1c6fd8; border-color: #1c6fd8; color: #fff; }

        .invoice {
            background: #fff;
            margin: 0 auto 30px;
            padding: 24px;
        }
        .invoice.a4 { width: 210mm; min-height: 297mm; }
        .invoice.receipt { width: 80mm; padding: 10px; font-size: 11px; }

        .shop-header { text-align: center; border-bottom: 2px solid #212529; padding-bottom: 10px; margin-bottom: 12px; }
        .shop-header h1 { font-size: 20px; margin-bottom: 4px; }
        .receipt .shop-header h1 { font-size: 14px; }
        .shop-header p { font-size: 12px; color: #495057; }
        .receipt .shop-header p { font-size: 10px; }

        .meta { display: flex; flex-wrap: wrap; gap: 4px 18px; font-size: 13px; margin-bottom: 12px; }
        .receipt .meta { font-size: 10px; flex-direction: column; gap: 2px; }
        .meta div span { color: #495057; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.items th, table.items td {
            border: 1px solid #adb5bd;
            padding: 6px 8px;
            font-size: 13px;
            text-align: right;
        }
        .receipt table.items th, .receipt table.items td { padding: 3px 4px; font-size: 10px; border-color: #dee2e6; }
        table.items thead th { background: #e9ecef; }

        .totals { width: 260px; margin-inline-start: auto; font-size: 13px; }
        .receipt .totals { width: 100%; font-size: 10px; }
        .totals .row { display: flex; justify-content: space-between; padding: 3px 0; }
        .totals .row.net { font-weight: bold; font-size: 15px; border-top: 1px solid #212529; margin-top: 4px; padding-top: 6px; }
        .receipt .totals .row.net { font-size: 12px; }

        .shop-logo { max-height: 64px; max-width: 160px; object-fit: contain; margin-bottom: 6px; }
        .receipt .shop-logo { max-height: 44px; max-width: 120px; }

        .invoice-footer {
            margin-top: 22px;
            border-top: 1px dashed #adb5bd;
            padding-top: 10px;
            text-align: center;
            font-size: 12px;
            color: #495057;
        }
        .receipt .invoice-footer { font-size: 9px; margin-top: 12px; padding-top: 6px; }
        .invoice-footer .shop-contact { margin-bottom: 4px; }

        .thanks { text-align: center; margin-top: 8px; font-size: 13px; color: #495057; }
        .receipt .thanks { font-size: 10px; margin-top: 6px; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .invoice { margin: 0; }
            .invoice.a4 { width: auto; min-height: auto; }
        }
        @page { margin: 8mm; }
    </style>
</head>
<body>

    <div class="toolbar">
        <button type="button" class="primary" onclick="window.print()">{{ __('messages.actions.print') }}</button>
        @if ($mode === 'receipt')
            <a href="{{ route('invoices.show', $sale) }}">{{ __('messages.invoices.print_a4') }}</a>
        @else
            <a href="{{ route('invoices.show', ['sale' => $sale, 'mode' => 'receipt']) }}">{{ __('messages.invoices.print_receipt') }}</a>
        @endif
        @can('manage-returns')
            <a href="{{ route('returns.create', ['invoice' => $sale->invoice_number]) }}">{{ __('messages.returns.create') }}</a>
        @endcan
        <a href="{{ route('invoices.index') }}">{{ __('messages.actions.back') }}</a>
    </div>

    <div class="invoice {{ $mode }}">

        <div class="shop-header">
            @if ($shop->hasMedia('logo'))
                <img src="{{ $shop->getFirstMediaUrl('logo') }}" alt="{{ $shop->displayName() }}" class="shop-logo">
            @endif
            <h1>{{ $shop->displayName() }}</h1>
        </div>

        <div class="meta">
            <div><span>{{ __('messages.invoice_print.invoice_number') }}:</span> <strong>{{ $sale->invoice_number }}</strong></div>
            <div><span>{{ __('messages.invoice_print.date') }}:</span> {{ $sale->date->format('Y-m-d') }}</div>
            <div><span>{{ __('messages.invoice_print.client') }}:</span> {{ $sale->client_name ?: __('messages.invoices.cash_client') }}</div>
            @if ($sale->client_phone)
                <div><span>{{ __('messages.invoice_print.phone') }}:</span> <span dir="ltr">{{ $sale->client_phone }}</span></div>
            @endif
            <div><span>{{ __('messages.invoice_print.seller') }}:</span> {{ $sale->user?->name }}</div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>{{ __('messages.invoice_print.item') }}</th>
                    <th>{{ __('messages.invoice_print.qty') }}</th>
                    <th>{{ __('messages.invoice_print.price') }}</th>
                    <th>{{ __('messages.invoice_print.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td>{{ $item->product?->name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="row">
                <span>{{ __('messages.invoice_print.subtotal') }}</span>
                <span>{{ number_format($sale->total_amount, 2) }} {{ __('messages.currency') }}</span>
            </div>
            @if ($sale->sale_amount > 0)
                <div class="row">
                    <span>{{ __('messages.invoice_print.discount') }}</span>
                    <span>{{ number_format($sale->sale_amount, 2) }} {{ __('messages.currency') }}</span>
                </div>
            @endif
            <div class="row net">
                <span>{{ __('messages.invoice_print.net_total') }}</span>
                <span>{{ number_format($sale->total_after_sale, 2) }} {{ __('messages.currency') }}</span>
            </div>
            <div class="row">
                <span>{{ __('messages.invoice_print.paid') }}</span>
                <span>{{ number_format($sale->paid_amount, 2) }} {{ __('messages.currency') }}</span>
            </div>
            <div class="row">
                <span>{{ __('messages.invoice_print.remaining') }}</span>
                <span>{{ number_format($sale->remaining_amount, 2) }} {{ __('messages.currency') }}</span>
            </div>
            <div class="row">
                <span>{{ __('messages.invoice_print.payment_method') }}</span>
                <span>{{ $sale->payment_method->label() }}</span>
            </div>
        </div>

        <div class="invoice-footer">
            <div class="shop-contact">
                {{ __('messages.invoice_print.shop_address') }}: {{ $shop->displayAddress() }}
                — {{ __('messages.invoice_print.shop_phone') }}: <span dir="ltr">{{ $shop->displayPhone() }}</span>
            </div>
            <p class="thanks">{{ __('messages.invoices.thanks') }}</p>
        </div>
    </div>

</body>
</html>
