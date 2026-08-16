<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierPurchase;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->supplier = Supplier::factory()->create();
    $this->category = Category::factory()->create();
});

test('a purchase upserts products by code', function () {
    $existing = Product::factory()->create([
        'code' => 'EXIST-01',
        'quantity' => 5,
        'purchase_price' => 10,
        'selling_price' => 15,
        'wholesale_price' => 12,
    ]);

    $response = $this->actingAs($this->admin)->post('/purchases', [
        'date' => now()->toDateString(),
        'supplier_id' => $this->supplier->id,
        'amount_paid' => 100,
        'sale_price' => null,
        'items' => [
            [
                'name' => 'منتج موجود',
                'code' => 'EXIST-01',
                'category_id' => $this->category->id,
                'purchase_price' => 20,
                'selling_price' => 30,
                'wholesale_price' => 25,
                'quantity' => 7,
            ],
            [
                'name' => 'منتج جديد',
                'code' => 'NEW-01',
                'category_id' => $this->category->id,
                'purchase_price' => 50,
                'selling_price' => 80,
                'wholesale_price' => 65,
                'quantity' => 3,
            ],
        ],
    ]);

    $response->assertRedirect(route('purchases.index', absolute: false));

    // المنتج الموجود: زيادة الكمية وتحديث الأسعار
    $existing->refresh();
    expect($existing->quantity)->toBe(12)
        ->and((float) $existing->purchase_price)->toBe(20.00)
        ->and((float) $existing->selling_price)->toBe(30.00)
        ->and((float) $existing->wholesale_price)->toBe(25.00);

    // المنتج الجديد: يُنشأ مرتبطًا بمورد الفاتورة
    $new = Product::where('code', 'NEW-01')->first();
    expect($new)->not->toBeNull()
        ->and($new->quantity)->toBe(3)
        ->and($new->supplier_id)->toBe($this->supplier->id)
        ->and($new->category_id)->toBe($this->category->id);

    // الإجمالي والمتبقي محسوبان من الأصناف وليس من النموذج
    $purchase = SupplierPurchase::first();
    expect((float) $purchase->total_amount)->toBe(290.00) // 20*7 + 50*3
        ->and((float) $purchase->remaining_amount)->toBe(190.00);
});

test('deleting a purchase reverses its stock changes', function () {
    $this->actingAs($this->admin)->post('/purchases', [
        'date' => now()->toDateString(),
        'supplier_id' => $this->supplier->id,
        'amount_paid' => 0,
        'items' => [
            [
                'name' => 'منتج',
                'code' => 'DEL-01',
                'category_id' => $this->category->id,
                'purchase_price' => 10,
                'selling_price' => 15,
                'wholesale_price' => 12,
                'quantity' => 8,
            ],
        ],
    ]);

    $product = Product::where('code', 'DEL-01')->first();
    expect($product->quantity)->toBe(8);

    $this->actingAs($this->admin)
        ->delete(route('purchases.destroy', SupplierPurchase::first()))
        ->assertRedirect(route('purchases.index', absolute: false));

    expect($product->refresh()->quantity)->toBe(0)
        ->and(SupplierPurchase::count())->toBe(0);
});

test('a purchase item image is attached to the product', function () {
    $image = \Illuminate\Http\UploadedFile::fake()->image('product.png', 200, 200);

    $this->actingAs($this->admin)->post('/purchases', [
        'date' => now()->toDateString(),
        'supplier_id' => $this->supplier->id,
        'amount_paid' => 0,
        'items' => [
            [
                'name' => 'منتج بصورة',
                'code' => 'IMG-01',
                'main_category_id' => $this->category->id,
                'category_id' => $this->category->id,
                'purchase_price' => 10,
                'selling_price' => 15,
                'wholesale_price' => 12,
                'quantity' => 2,
                'image' => $image,
            ],
        ],
    ])->assertRedirect(route('purchases.index', absolute: false));

    $product = Product::where('code', 'IMG-01')->first();

    expect($product)->not->toBeNull()
        ->and($product->hasMedia('image'))->toBeTrue();
});

test('a purchase item can be assigned to a subcategory', function () {
    $child = Category::factory()->create(['parent_id' => $this->category->id]);

    $this->actingAs($this->admin)->post('/purchases', [
        'date' => now()->toDateString(),
        'supplier_id' => $this->supplier->id,
        'amount_paid' => 0,
        'items' => [
            [
                'name' => 'منتج فرعي',
                'code' => 'SUB-01',
                'main_category_id' => $this->category->id,
                'category_id' => $child->id,
                'purchase_price' => 10,
                'selling_price' => 15,
                'wholesale_price' => 12,
                'quantity' => 1,
            ],
        ],
    ])->assertRedirect(route('purchases.index', absolute: false));

    expect(Product::where('code', 'SUB-01')->first()->category_id)->toBe($child->id);
});

test('purchase totals are computed server side', function () {
    $this->actingAs($this->admin)->post('/purchases', [
        'date' => now()->toDateString(),
        'supplier_id' => $this->supplier->id,
        'amount_paid' => 30,
        'items' => [
            [
                'name' => 'صنف',
                'code' => 'CALC-01',
                'category_id' => $this->category->id,
                'purchase_price' => 12.5,
                'selling_price' => 20,
                'wholesale_price' => 16,
                'quantity' => 4,
            ],
        ],
    ]);

    $purchase = SupplierPurchase::first();

    expect((float) $purchase->total_amount)->toBe(50.00)
        ->and((float) $purchase->remaining_amount)->toBe(20.00);
});
