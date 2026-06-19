<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (auth()->user()->type === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('shop.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard', [
            'totalSales' => 0,
            'totalCommissionPaid' => 0,
            'totalCommissionPending' => 0,
            'totalUsers' => 0,
            'totalStores' => 0,
            'totalSellers' => 0,
        ]);
    })->name('dashboard');

    // Dummy routes for Admin sidebar
    Route::get('/users', function() { return 'Users UI Pending'; })->name('users.index');
    Route::get('/sellers', function() { return 'Sellers UI Pending'; })->name('sellers.index');
    Route::get('/payments', function() { return 'Payments UI Pending'; })->name('payments.index');
    Route::get('/packages', function() { return 'Packages UI Pending'; })->name('packages.index');
    Route::get('/wallets', function() { return 'Wallets UI Pending'; })->name('wallets.index');
});

Route::middleware(['auth', 'role:shop'])->prefix('shop')->name('shop.')->group(function () {
    // API Routes for frontend JS
    Route::get('/api/products', [ProductController::class, 'apiIndex'])->name('api.products.index');
    Route::post('/api/products', [ProductController::class, 'store'])->name('api.products.store');
    Route::put('/api/products/{product}', [ProductController::class, 'update'])->name('api.products.update');
    Route::delete('/api/products/{product}', [ProductController::class, 'destroy'])->name('api.products.destroy');

    Route::get('/api/categories', [CategoryController::class, 'apiIndex'])->name('api.categories.index');
    Route::post('/api/categories', [CategoryController::class, 'store'])->name('api.categories.store');
    Route::put('/api/categories/{category}', [CategoryController::class, 'update'])->name('api.categories.update');
    Route::delete('/api/categories/{category}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');

    Route::get('/api/customers', [CustomerController::class, 'apiIndex'])->name('api.customers.index');
    Route::post('/api/customers', [CustomerController::class, 'store'])->name('api.customers.store');
    Route::post('/api/customers/{customer}', [CustomerController::class, 'update'])->name('api.customers.update'); // POST instead of PUT because of FormData file uploads
    Route::delete('/api/customers/{customer}', [CustomerController::class, 'destroy'])->name('api.customers.destroy');

    Route::get('/dashboard', function () {
        return view('shop.dashboard');
    })->name('dashboard');
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/invoices', function() { return 'Invoices UI Pending'; })->name('invoices.index');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/expenses', function() { return 'Expenses UI Pending'; })->name('expenses.index');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/medicines', function() { return 'Medicines UI Pending'; })->name('medicines.index');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/alerts', function() { return 'Alerts UI Pending'; })->name('alerts.index');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/settings/print', function() { return 'Settings UI Pending'; })->name('settings.print');
});

// Seller Dummy Routes
Route::middleware(['auth'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard', function() { return 'Seller Dashboard UI Pending'; })->name('dashboard');
    Route::get('/commissions', function() { return 'Seller Commissions UI Pending'; })->name('commissions');
    Route::get('/payout', function() { return 'Seller Payout UI Pending'; })->name('payout');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
