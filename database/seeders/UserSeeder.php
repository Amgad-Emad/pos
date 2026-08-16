<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = Str::password(12, symbols: false);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@pos.test'],
            [
                'name' => 'مدير النظام',
                'password' => $adminPassword,
                'active' => true,
            ],
        );
        $admin->syncRoles('admin');

        if ($admin->wasRecentlyCreated) {
            $this->command?->warn('حساب المدير: admin@pos.test');
            $this->command?->warn("كلمة مرور المدير: {$adminPassword}");
        }

        foreach ([
            ['name' => 'أحمد البائع', 'email' => 'seller1@pos.test'],
            ['name' => 'محمد البائع', 'email' => 'seller2@pos.test'],
        ] as $sellerData) {
            $seller = User::query()->firstOrCreate(
                ['email' => $sellerData['email']],
                [
                    'name' => $sellerData['name'],
                    'password' => 'password',
                    'active' => true,
                ],
            );
            $seller->syncRoles('seller');
        }

        $this->command?->info('حسابات البائعين: seller1@pos.test و seller2@pos.test — كلمة المرور: password');
    }
}
