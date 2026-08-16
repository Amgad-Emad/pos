<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $product = Product::factory()->create(['quantity' => 50]);
    $service = app(SaleService::class);

    $this->saleA = $service->create([
        'client_name' => 'محمود عبد الرحمن',
        'client_phone' => '01055554444',
        'date' => now()->toDateString(),
        'sale_amount' => 0,
        'paid_amount' => 0,
        'payment_method' => 'cash',
        'items' => [['product_id' => $product->id, 'qty' => 1]],
    ], $this->admin);

    $this->saleB = $service->create([
        'client_name' => 'هالة سمير',
        'client_phone' => '01299998888',
        'date' => now()->toDateString(),
        'sale_amount' => 0,
        'paid_amount' => 0,
        'payment_method' => 'cash',
        'items' => [['product_id' => $product->id, 'qty' => 1]],
    ], $this->admin);
});

test('invoices can be searched by invoice number', function () {
    $this->actingAs($this->admin)
        ->get('/invoices?q='.$this->saleA->invoice_number)
        ->assertOk()
        ->assertSee($this->saleA->invoice_number)
        ->assertDontSee($this->saleB->invoice_number);
});

test('invoices can be searched by client name', function () {
    $this->actingAs($this->admin)
        ->get('/invoices?q='.urlencode('هالة'))
        ->assertOk()
        ->assertSee($this->saleB->invoice_number)
        ->assertDontSee($this->saleA->invoice_number);
});

test('invoices can be searched by client phone', function () {
    $this->actingAs($this->admin)
        ->get('/invoices?q=01055554444')
        ->assertOk()
        ->assertSee($this->saleA->invoice_number)
        ->assertDontSee($this->saleB->invoice_number);
});

test('invoice search combines with other filters', function () {
    $this->actingAs($this->admin)
        ->get('/invoices?q='.urlencode('محمود').'&payment_method=instapay')
        ->assertOk()
        ->assertDontSee($this->saleA->invoice_number)
        ->assertDontSee($this->saleB->invoice_number);
});

test('seller search only covers their own invoices', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    // فاتورة المدير تحمل اسم "محمود" لكن البائع لا يملكها
    $this->actingAs($seller)
        ->get('/invoices?q='.urlencode('محمود'))
        ->assertOk()
        ->assertDontSee($this->saleA->invoice_number);
});
