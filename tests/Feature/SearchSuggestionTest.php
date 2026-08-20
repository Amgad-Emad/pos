<?php

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SaleService;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->seller = User::factory()->create();
    $this->seller->assignRole('seller');
});

test('product suggestions match the typed term', function () {
    $product = Product::factory()->create(['name' => 'شاشة سامسونج', 'code' => 'PRD-1001']);
    Product::factory()->create(['name' => 'كيبورد لوجيتك', 'code' => 'PRD-1002']);

    $response = $this->actingAs($this->admin)->getJson(route('search.suggestions', ['type' => 'products', 'q' => 'شاشة']));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.label', 'شاشة سامسونج')
        ->assertJsonPath('0.meta', 'PRD-1001')
        ->assertJsonPath('0.url', route('products.edit', $product));
});

test('suggestions match partially typed terms character by character', function () {
    Product::factory()->create(['name' => 'ماوس لاسلكي', 'code' => 'PRD-2001']);

    foreach (['م', 'ما', 'ماو', 'ماوس'] as $term) {
        $this->actingAs($this->admin)
            ->getJson(route('search.suggestions', ['type' => 'products', 'q' => $term]))
            ->assertOk()
            ->assertJsonPath('0.label', 'ماوس لاسلكي');
    }
});

test('an empty term returns no suggestions', function () {
    Product::factory()->create(['name' => 'ماوس لاسلكي']);

    $this->actingAs($this->admin)
        ->getJson(route('search.suggestions', ['type' => 'products', 'q' => '']))
        ->assertOk()
        ->assertExactJson([]);
});

test('supplier suggestions match name or phone', function () {
    $supplier = Supplier::factory()->create(['name' => 'شركة النور', 'phone' => '01011112222']);

    $this->actingAs($this->admin)
        ->getJson(route('search.suggestions', ['type' => 'suppliers', 'q' => '0101']))
        ->assertOk()
        ->assertJsonPath('0.label', 'شركة النور')
        ->assertJsonPath('0.url', route('suppliers.show', $supplier));
});

test('invoice suggestions are limited to the seller own sales', function () {
    $product = Product::factory()->create(['quantity' => 50]);
    $service = app(SaleService::class);

    $payload = fn (string $client) => [
        'client_name' => $client,
        'client_phone' => '01000000000',
        'date' => now()->toDateString(),
        'sale_amount' => 0,
        'paid_amount' => 0,
        'payment_method' => 'cash',
        'items' => [['product_id' => $product->id, 'qty' => 1]],
    ];

    $ownSale = $service->create($payload('عميل البائع'), $this->seller);
    $service->create($payload('عميل المدير'), $this->admin);

    $this->actingAs($this->seller)
        ->getJson(route('search.suggestions', ['type' => 'invoices', 'q' => 'عميل']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.label', $ownSale->invoice_number);

    $this->actingAs($this->admin)
        ->getJson(route('search.suggestions', ['type' => 'invoices', 'q' => 'عميل']))
        ->assertOk()
        ->assertJsonCount(2);
});

test('suggestions require the matching permission', function () {
    $this->actingAs($this->seller)
        ->getJson(route('search.suggestions', ['type' => 'products', 'q' => 'a']))
        ->assertForbidden();

    $this->actingAs($this->seller)
        ->getJson(route('search.suggestions', ['type' => 'users', 'q' => 'a']))
        ->assertForbidden();
});

test('an unknown suggestion type is not routable', function () {
    $this->actingAs($this->admin)->getJson('/search/suggestions/orders?q=a')->assertNotFound();
});

test('guests cannot read suggestions', function () {
    $this->getJson(route('search.suggestions', ['type' => 'products', 'q' => 'a']))->assertUnauthorized();
});

test('search screens are wired to the live suggestions endpoint', function (string $route, string $type) {
    // المسار داخل الصفحة مُرمَّز بصيغة JSON (الشرطات المائلة مسبوقة بـ \\).
    $url = trim(json_encode(route('search.suggestions', $type)), '"');

    $this->actingAs($this->admin)->get($route)->assertOk()->assertSee($url, false);
})->with([
    ['/products', 'products'],
    ['/inventory', 'inventory'],
    ['/suppliers', 'suppliers'],
    ['/purchases', 'purchases'],
    ['/sales', 'sales'],
    ['/returns', 'returns'],
    ['/users', 'users'],
    ['/invoices', 'invoices'],
    ['/returns/create', 'returns-sale'],
]);

test('the suggestions component is loaded once per page', function () {
    $html = $this->actingAs($this->admin)->get('/products')->assertOk()->getContent();

    expect(substr_count($html, 'function searchSuggest('))->toBe(1);
});
