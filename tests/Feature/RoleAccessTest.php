<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->seller = User::factory()->create();
    $this->seller->assignRole('seller');
});

test('seller cannot access admin routes', function (string $route) {
    $this->actingAs($this->seller)->get($route)->assertForbidden();
})->with([
    '/dashboard',
    '/products',
    '/categories',
    '/suppliers',
    '/purchases',
    '/users',
]);

test('admin can access admin routes', function (string $route) {
    $this->actingAs($this->admin)->get($route)->assertOk();
})->with([
    '/dashboard',
    '/products',
    '/categories',
    '/suppliers',
    '/purchases',
    '/users',
]);

test('seller can access sales, inventory and invoices', function (string $route) {
    $this->actingAs($this->seller)->get($route)->assertOk();
})->with([
    '/sales',
    '/sales/create',
    '/inventory',
    '/invoices',
    '/profile',
]);

test('wholesale price is never rendered in inventory for sellers', function () {
    $product = Product::factory()->create([
        'wholesale_price' => 123456.78,
        'purchase_price' => 87654.32,
    ]);

    $response = $this->actingAs($this->seller)->get('/inventory');

    $response->assertOk();
    $response->assertDontSee('123,456.78');
    $response->assertDontSee('87,654.32');
    $response->assertDontSee(__('messages.fields.wholesale_price'));
});

test('wholesale price is rendered in inventory for admin', function () {
    Product::factory()->create(['wholesale_price' => 123456.78]);

    $response = $this->actingAs($this->admin)->get('/inventory');

    $response->assertOk();
    $response->assertSee('123,456.78');
});

test('seller cannot manage another sellers sale', function () {
    $otherSeller = User::factory()->create();
    $otherSeller->assignRole('seller');

    $product = Product::factory()->create(['quantity' => 10]);

    $sale = app(\App\Services\SaleService::class)->create([
        'client_name' => null,
        'client_phone' => null,
        'date' => now()->toDateString(),
        'sale_amount' => 0,
        'paid_amount' => 0,
        'payment_method' => 'cash',
        'items' => [['product_id' => $product->id, 'qty' => 1]],
    ], $otherSeller);

    $this->actingAs($this->seller)->get(route('sales.edit', $sale))->assertForbidden();
    $this->actingAs($this->seller)->delete(route('sales.destroy', $sale))->assertForbidden();
    $this->actingAs($this->seller)->get(route('invoices.show', $sale))->assertForbidden();
});
