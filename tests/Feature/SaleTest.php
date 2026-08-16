<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->seller = User::factory()->create();
    $this->seller->assignRole('seller');
});

test('creating a sale decrements product stock', function () {
    $product = Product::factory()->create(['quantity' => 10, 'selling_price' => 100]);

    $response = $this->actingAs($this->seller)->post('/sales', [
        'client_name' => 'عميل تجريبي',
        'client_phone' => '01000000000',
        'date' => now()->toDateString(),
        'sale_amount' => 20,
        'paid_amount' => 250,
        'payment_method' => 'cash',
        'items' => [
            ['product_id' => $product->id, 'qty' => 3],
        ],
    ]);

    $sale = Sale::first();

    $response->assertRedirect(route('invoices.show', $sale, absolute: false));

    expect($product->refresh()->quantity)->toBe(7)
        ->and($sale->invoice_number)->toBe('INV-000001')
        ->and((float) $sale->total_amount)->toBe(300.00)
        ->and((float) $sale->total_after_sale)->toBe(280.00)
        ->and((float) $sale->remaining_amount)->toBe(30.00)
        ->and($sale->user_id)->toBe($this->seller->id);
});

test('overselling is blocked with an arabic error and stock is unchanged', function () {
    $product = Product::factory()->create(['quantity' => 2]);

    $response = $this->actingAs($this->seller)->post('/sales', [
        'date' => now()->toDateString(),
        'paid_amount' => 0,
        'payment_method' => 'cash',
        'items' => [
            ['product_id' => $product->id, 'qty' => 5],
        ],
    ]);

    $response->assertSessionHasErrors('items');

    expect(session('errors')->first('items'))
        ->toBe(__('messages.errors.insufficient_stock', [
            'product' => $product->name,
            'available' => 2,
        ]))
        ->and($product->refresh()->quantity)->toBe(2)
        ->and(Sale::count())->toBe(0);
});

test('deleting a sale restores product stock', function () {
    $product = Product::factory()->create(['quantity' => 10]);

    $this->actingAs($this->seller)->post('/sales', [
        'date' => now()->toDateString(),
        'paid_amount' => 0,
        'payment_method' => 'cash',
        'items' => [['product_id' => $product->id, 'qty' => 4]],
    ]);

    expect($product->refresh()->quantity)->toBe(6);

    $this->actingAs($this->seller)
        ->delete(route('sales.destroy', Sale::first()))
        ->assertRedirect(route('sales.index', absolute: false));

    expect($product->refresh()->quantity)->toBe(10)
        ->and(Sale::count())->toBe(0);
});

test('editing a sale reverses then reapplies stock', function () {
    $product = Product::factory()->create(['quantity' => 10]);

    $this->actingAs($this->seller)->post('/sales', [
        'date' => now()->toDateString(),
        'paid_amount' => 0,
        'payment_method' => 'cash',
        'items' => [['product_id' => $product->id, 'qty' => 4]],
    ]);

    $sale = Sale::first();

    $this->actingAs($this->seller)->put(route('sales.update', $sale), [
        'date' => now()->toDateString(),
        'paid_amount' => 0,
        'payment_method' => 'instapay',
        'items' => [['product_id' => $product->id, 'qty' => 9]],
    ])->assertRedirect(route('invoices.show', $sale, absolute: false));

    expect($product->refresh()->quantity)->toBe(1)
        ->and($sale->refresh()->items()->first()->qty)->toBe(9);
});

test('invoice numbers are sequential', function () {
    $product = Product::factory()->create(['quantity' => 10]);

    foreach (range(1, 2) as $i) {
        $this->actingAs($this->seller)->post('/sales', [
            'date' => now()->toDateString(),
            'paid_amount' => 0,
            'payment_method' => 'cash',
            'items' => [['product_id' => $product->id, 'qty' => 1]],
        ]);
    }

    expect(Sale::orderBy('id')->pluck('invoice_number')->all())
        ->toBe(['INV-000001', 'INV-000002']);
});
