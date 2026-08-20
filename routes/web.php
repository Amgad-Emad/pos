<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SearchSuggestionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if (! $request->user()) {
        return redirect()->route('login');
    }

    return $request->user()->can('view-dashboard')
        ? redirect()->route('dashboard')
        : redirect()->route('sales.index');
})->name('home');

Route::middleware(['auth', 'active'])->group(function () {
    // اقتراحات البحث الفورية لكل حقول البحث في الشاشات.
    Route::get('search/suggestions/{type}', SearchSuggestionController::class)
        ->whereIn('type', SearchSuggestionController::TYPES)
        ->name('search.suggestions');

    Route::get('pos/dashboard', DashboardController::class)
        ->middleware('permission:view-dashboard')
        ->name('dashboard');

    Route::get('inventory', [InventoryController::class, 'index'])
        ->middleware('permission:view-inventory')
        ->name('inventory.index');

    Route::middleware('permission:manage-sales')->group(function () {
        Route::get('sales/search-products', [SaleController::class, 'searchProducts'])
            ->name('sales.search-products');
        Route::resource('sales', SaleController::class)->except('show');
    });

    Route::middleware('permission:manage-returns')->group(function () {
        Route::get('returns/search-sale', [ReturnController::class, 'searchSale'])
            ->name('returns.search-sale');
        Route::resource('returns', ReturnController::class)
            ->only(['index', 'create', 'store', 'show', 'destroy']);
    });

    Route::middleware('permission:view-invoices')->group(function () {
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{sale}', [InvoiceController::class, 'show'])->name('invoices.show');
    });

    Route::resource('products', ProductController::class)
        ->except('show')
        ->middleware('permission:manage-products');

    Route::resource('categories', CategoryController::class)
        ->except('show')
        ->middleware('permission:manage-categories');

    Route::resource('suppliers', SupplierController::class)
        ->middleware('permission:manage-suppliers');

    Route::resource('purchases', PurchaseController::class)
        ->parameters(['purchases' => 'purchase'])
        ->middleware('permission:manage-purchases');

    Route::resource('users', UserController::class)
        ->except('show')
        ->middleware('permission:manage-users');

    Route::middleware('permission:manage-settings')->group(function () {
        Route::get('settings', [\App\Http\Controllers\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    });

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

require __DIR__.'/auth.php';
