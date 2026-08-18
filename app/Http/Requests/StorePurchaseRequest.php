<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-purchases');
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.code' => ['nullable', 'string', 'max:100'],
            'items.*.main_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'items.*.category_id' => ['required', 'integer', 'exists:categories,id'],
            'items.*.image' => ['nullable', 'image', 'max:20480'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'items.*.selling_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'items.*.wholesale_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $total = collect($this->validated('items', []))->sum(
                    fn (array $item) => (float) $item['purchase_price'] * (int) $item['quantity']
                );

                if ((float) $this->validated('amount_paid') > round($total, 2)) {
                    $validator->errors()->add(
                        'amount_paid',
                        __('validation.lte.numeric', [
                            'attribute' => __('validation.attributes.amount_paid'),
                            'value' => number_format($total, 2),
                        ]),
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => __('messages.errors.items_required'),
            'items.min' => __('messages.errors.items_required'),
        ];
    }
}
