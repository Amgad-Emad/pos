<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
        ]);

        if (! app()->environment('production') && Product::query()->count() === 0) {
            $this->seedSampleData();
        }
    }

    /**
     * بيانات تجريبية للتطوير حتى لا تكون لوحة التحكم فارغة.
     */
    protected function seedSampleData(): void
    {
        $electronics = Category::create(['name' => 'إلكترونيات']);
        $phones = Category::create(['name' => 'هواتف محمولة', 'parent_id' => $electronics->id]);
        $accessories = Category::create(['name' => 'إكسسوارات', 'parent_id' => $electronics->id]);
        $clothes = Category::create(['name' => 'ملابس']);
        $men = Category::create(['name' => 'ملابس رجالي', 'parent_id' => $clothes->id]);
        $home = Category::create(['name' => 'أدوات منزلية']);

        $suppliers = collect([
            Supplier::create(['name' => 'شركة النور للتوريدات', 'phone' => '01001234567', 'address' => 'القاهرة - وسط البلد']),
            Supplier::create(['name' => 'مؤسسة الأمل التجارية', 'phone' => '01119876543', 'address' => 'الجيزة - المهندسين']),
            Supplier::create(['name' => 'مكتب السلام للجملة', 'phone' => '01225554443', 'address' => null]),
        ]);

        $products = [
            ['سماعة بلوتوث لاسلكية', 'ACC-0001', $accessories, 150, 250, 200, 25],
            ['شاحن سريع 65 واط', 'ACC-0002', $accessories, 200, 350, 280, 40],
            ['كابل شحن تايب سي', 'ACC-0003', $accessories, 30, 60, 45, 3],
            ['هاتف ذكي 128 جيجا', 'PHN-0001', $phones, 8000, 9500, 8800, 12],
            ['هاتف اقتصادي 64 جيجا', 'PHN-0002', $phones, 3500, 4200, 3900, 8],
            ['جراب هاتف سيليكون', 'ACC-0004', $accessories, 25, 60, 40, 4],
            ['قميص قطن رجالي', 'MEN-0001', $men, 180, 300, 240, 30],
            ['بنطلون جينز رجالي', 'MEN-0002', $men, 250, 420, 340, 20],
            ['طقم أكواب زجاج', 'HOM-0001', $home, 90, 160, 125, 15],
            ['مقلاة تيفال 26 سم', 'HOM-0002', $home, 320, 500, 420, 5],
        ];

        foreach ($products as $i => [$name, $code, $category, $purchase, $selling, $wholesale, $qty]) {
            Product::create([
                'supplier_id' => $suppliers[$i % $suppliers->count()]->id,
                'category_id' => $category->id,
                'name' => $name,
                'code' => $code,
                'purchase_price' => $purchase,
                'selling_price' => $selling,
                'wholesale_price' => $wholesale,
                'quantity' => $qty,
            ]);
        }

        $this->seedSampleSales();
    }

    /**
     * مبيعات تجريبية موزعة على آخر 30 يومًا لعرض الرسوم البيانية.
     */
    protected function seedSampleSales(): void
    {
        $saleService = app(SaleService::class);
        $sellers = User::role('seller')->get();
        $methods = PaymentMethod::cases();
        $clients = ['محمد أحمد', 'خالد يوسف', 'سارة علي', null, 'منى إبراهيم', null];

        for ($i = 0; $i < 15; $i++) {
            $products = Product::query()->where('quantity', '>', 3)->inRandomOrder()->take(rand(1, 3))->get();

            if ($products->isEmpty()) {
                break;
            }

            $items = $products->map(fn (Product $product) => [
                'product_id' => $product->id,
                'qty' => rand(1, min(2, $product->quantity)),
            ])->all();

            $total = collect($items)->sum(
                fn (array $item) => Product::find($item['product_id'])->selling_price * $item['qty']
            );
            $discount = rand(0, 1) ? round($total * 0.05, 2) : 0;
            $totalAfter = round($total - $discount, 2);

            $saleService->create([
                'client_name' => $clients[array_rand($clients)],
                'client_phone' => null,
                'date' => now()->subDays(rand(0, 29))->toDateString(),
                'sale_amount' => $discount,
                'paid_amount' => rand(0, 3) ? $totalAfter : round($totalAfter * 0.5, 2),
                'payment_method' => $methods[array_rand($methods)]->value,
                'items' => $items,
            ], $sellers->random());
        }
    }
}
