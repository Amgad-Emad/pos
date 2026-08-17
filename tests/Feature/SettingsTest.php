<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\ShopSetting;
use App\Models\User;
use App\Services\SaleService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->seller = User::factory()->create();
    $this->seller->assignRole('seller');
});

test('admin can view the settings page', function () {
    $this->actingAs($this->admin)->get('/settings')->assertOk();
});

test('seller cannot access the settings page', function () {
    $this->actingAs($this->seller)->get('/settings')->assertForbidden();
    $this->actingAs($this->seller)->put('/settings', [
        'name' => 'x', 'phone' => '0100', 'address' => 'y',
    ])->assertForbidden();
});

test('admin can update shop name, phone and address', function () {
    $response = $this->actingAs($this->admin)->put('/settings', [
        'name' => 'متجر الاختبار',
        'phone' => '01234567890',
        'address' => 'شارع التجربة، القاهرة',
    ]);

    $response->assertRedirect(route('settings.edit', absolute: false));

    $settings = ShopSetting::current()->fresh();

    expect($settings->name)->toBe('متجر الاختبار')
        ->and($settings->phone)->toBe('01234567890')
        ->and($settings->address)->toBe('شارع التجربة، القاهرة');
});

test('settings fields are required', function () {
    $this->actingAs($this->admin)
        ->put('/settings', ['name' => '', 'phone' => '', 'address' => ''])
        ->assertSessionHasErrors(['name', 'phone', 'address']);
});

test('admin can upload a shop logo', function () {
    $logo = UploadedFile::fake()->image('logo.png', 300, 300);

    $this->actingAs($this->admin)->put('/settings', [
        'name' => 'متجر الاختبار',
        'phone' => '01234567890',
        'address' => 'العنوان',
        'logo' => $logo,
    ])->assertRedirect(route('settings.edit', absolute: false));

    expect(ShopSetting::current()->fresh()->hasMedia('logo'))->toBeTrue();
});

test('shop address and phone appear on the printed invoice', function () {
    ShopSetting::current()->update([
        'name' => 'متجر الفاتورة',
        'phone' => '01099887766',
        'address' => 'عنوان يظهر أسفل الفاتورة',
    ]);

    $product = Product::factory()->create(['quantity' => 5]);

    $sale = app(SaleService::class)->create([
        'client_name' => null,
        'client_phone' => null,
        'date' => now()->toDateString(),
        'sale_amount' => 0,
        'paid_amount' => 0,
        'payment_method' => 'cash',
        'items' => [['product_id' => $product->id, 'qty' => 1]],
    ], $this->seller);

    foreach (['a4' => [], 'receipt' => ['mode' => 'receipt']] as $mode => $query) {
        $response = $this->actingAs($this->admin)->get(route('invoices.show', [$sale] + $query));

        $response->assertOk()
            ->assertSee('متجر الفاتورة')
            ->assertSee('عنوان يظهر أسفل الفاتورة')
            ->assertSee('01099887766');
    }
});

test('invoice shows no placeholder shop name when settings are empty', function () {
    $product = Product::factory()->create(['quantity' => 5]);

    $sale = app(SaleService::class)->create([
        'client_name' => null,
        'client_phone' => null,
        'date' => now()->toDateString(),
        'sale_amount' => 0,
        'paid_amount' => 0,
        'payment_method' => 'cash',
        'items' => [['product_id' => $product->id, 'qty' => 1]],
    ], $this->seller);

    $this->actingAs($this->admin)
        ->get(route('invoices.show', $sale))
        ->assertOk()
        ->assertDontSee('متجر نقطة البيع')
        ->assertDontSee('العنوان الرئيسي للمتجر')
        ->assertDontSee(__('messages.invoice_print.shop_address').':');
});
