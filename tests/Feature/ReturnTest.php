<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->seller = User::factory()->create();
    $this->seller->assignRole('seller');
});

function createDiscountedSale($test): Sale
{
    // 5 أصناف × 200 = 1000 بخصم 250 → نسبة الخصم 25%.
    $test->product = Product::factory()->create(['quantity' => 10, 'selling_price' => 200]);

    $test->actingAs($test->seller)->post('/sales', [
        'date' => now()->toDateString(),
        'sale_amount' => 250,
        'paid_amount' => 750,
        'payment_method' => 'cash',
        'items' => [['product_id' => $test->product->id, 'qty' => 5]],
    ]);

    return Sale::first();
}

test('refund uses the discount-weighted price per unit', function () {
    $sale = createDiscountedSale($this);

    $this->actingAs($this->seller)->post('/returns', [
        'sale_id' => $sale->id,
        'date' => now()->toDateString(),
        'items' => [
            ['sale_item_id' => $sale->items()->first()->id, 'qty' => 1],
        ],
    ])->assertRedirect(route('returns.show', SaleReturn::first(), absolute: false));

    $saleReturn = SaleReturn::first();

    // سعر الوحدة 200 وخصم الفاتورة 25% → الاسترداد 150 وليس 200.
    expect((float) $saleReturn->total_refund)->toBe(150.00)
        ->and((float) $saleReturn->items()->first()->unit_refund)->toBe(150.00)
        ->and($saleReturn->return_number)->toBe('RET-000001')
        ->and($this->product->refresh()->quantity)->toBe(6);
});

test('returning more than the remaining quantity is blocked', function () {
    $sale = createDiscountedSale($this);
    $saleItem = $sale->items()->first();

    $this->actingAs($this->seller)->post('/returns', [
        'sale_id' => $sale->id,
        'date' => now()->toDateString(),
        'items' => [['sale_item_id' => $saleItem->id, 'qty' => 3]],
    ]);

    $response = $this->actingAs($this->seller)->post('/returns', [
        'sale_id' => $sale->id,
        'date' => now()->toDateString(),
        'items' => [['sale_item_id' => $saleItem->id, 'qty' => 3]],
    ]);

    $response->assertSessionHasErrors('items');

    expect(session('errors')->first('items'))
        ->toBe(__('messages.errors.return_qty_exceeds', [
            'product' => $this->product->name,
            'available' => 2,
        ]))
        ->and(SaleReturn::count())->toBe(1);
});

test('deleting a return removes the quantity from stock again', function () {
    $sale = createDiscountedSale($this);

    $this->actingAs($this->seller)->post('/returns', [
        'sale_id' => $sale->id,
        'date' => now()->toDateString(),
        'items' => [['sale_item_id' => $sale->items()->first()->id, 'qty' => 2]],
    ]);

    expect($this->product->refresh()->quantity)->toBe(7);

    $this->actingAs($this->seller)
        ->delete(route('returns.destroy', SaleReturn::first()))
        ->assertRedirect(route('returns.index', absolute: false));

    expect($this->product->refresh()->quantity)->toBe(5)
        ->and(SaleReturn::count())->toBe(0);
});

test('a sale with returns cannot be edited or deleted', function () {
    $sale = createDiscountedSale($this);

    $this->actingAs($this->seller)->post('/returns', [
        'sale_id' => $sale->id,
        'date' => now()->toDateString(),
        'items' => [['sale_item_id' => $sale->items()->first()->id, 'qty' => 1]],
    ]);

    $this->actingAs($this->seller)->delete(route('sales.destroy', $sale))
        ->assertSessionHasErrors('sale');

    $this->actingAs($this->seller)->put(route('sales.update', $sale), [
        'date' => now()->toDateString(),
        'paid_amount' => 0,
        'payment_method' => 'cash',
        'items' => [['product_id' => $this->product->id, 'qty' => 1]],
    ])->assertSessionHasErrors('sale');

    expect(Sale::count())->toBe(1);
});

test('search-sale returns items with returnable quantities and weighted refund', function () {
    $sale = createDiscountedSale($this);

    $this->actingAs($this->seller)->post('/returns', [
        'sale_id' => $sale->id,
        'date' => now()->toDateString(),
        'items' => [['sale_item_id' => $sale->items()->first()->id, 'qty' => 2]],
    ]);

    $response = $this->actingAs($this->seller)
        ->getJson(route('returns.search-sale', ['q' => $sale->invoice_number]));

    $response->assertOk()
        ->assertJsonPath('found', true)
        ->assertJsonPath('sale.items.0.unit_refund', 150)
        ->assertJsonPath('sale.items.0.returnable', 3);
});
