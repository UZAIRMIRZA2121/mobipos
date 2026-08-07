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

Route::middleware(['auth', 'role:shop,staff', 'check.privilege'])->prefix('shop')->name('shop.')->group(function () {
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

    Route::get('/api/expenses', [App\Http\Controllers\ExpenseController::class, 'apiIndex'])->name('api.expenses.index');
    Route::post('/api/expenses', [App\Http\Controllers\ExpenseController::class, 'store'])->name('api.expenses.store');
    Route::put('/api/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'update'])->name('api.expenses.update');
    Route::delete('/api/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'destroy'])->name('api.expenses.destroy');

    Route::get('/api/settings', [App\Http\Controllers\StoreSettingController::class, 'apiGet'])->name('api.settings.get');
    Route::post('/api/settings', [App\Http\Controllers\StoreSettingController::class, 'apiUpdate'])->name('api.settings.update');

    Route::get('/api/settings/print', [App\Http\Controllers\InvoiceSettingController::class, 'apiGet'])->name('api.settings.print.get');
    Route::post('/api/settings/print', [App\Http\Controllers\InvoiceSettingController::class, 'apiUpdate'])->name('api.settings.print.update');

    // Reports routes
    Route::get('/api/reports/generate', [App\Http\Controllers\ReportController::class, 'generate'])->name('api.reports.generate');
    Route::post('/api/reports/generate', [App\Http\Controllers\ReportController::class, 'generate'])->name('api.reports.generate.post');
    Route::get('/api/purchase-orders', [App\Http\Controllers\PurchaseOrderController::class, 'apiIndex'])->name('api.purchase_orders.index');
    Route::post('/api/purchase-orders', [App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('api.purchase_orders.store');
    Route::put('/api/purchase-orders/{purchaseOrder}', [App\Http\Controllers\PurchaseOrderController::class, 'update'])->name('api.purchase_orders.update');
    Route::delete('/api/purchase-orders/{purchaseOrder}', [App\Http\Controllers\PurchaseOrderController::class, 'destroy'])->name('api.purchase_orders.destroy');

    Route::get('/api/dashboard-stats', [App\Http\Controllers\OrderController::class, 'dashboardStats'])->name('api.dashboard.stats');
    Route::get('/api/orders', [App\Http\Controllers\OrderController::class, 'apiIndex'])->name('api.orders.index');
    Route::get('/api/orders/{order}', [App\Http\Controllers\OrderController::class, 'apiShow'])->name('api.orders.show');
    Route::put('/api/orders/{order}', [App\Http\Controllers\OrderController::class, 'update'])->name('api.orders.update');
    Route::delete('/api/orders/{order}', [App\Http\Controllers\OrderController::class, 'destroy'])->name('api.orders.destroy');
    Route::post('/api/orders/{order}/refund', [App\Http\Controllers\OrderController::class, 'refund'])->name('api.orders.refund');
    Route::post('/api/orders', [App\Http\Controllers\OrderController::class, 'store'])->name('api.orders.store');
    Route::get('/orders/{order}/invoice', [App\Http\Controllers\OrderController::class, 'invoice'])->name('orders.invoice');

    Route::get('/dashboard', function () {
        return view('shop.dashboard');
    })->name('dashboard');

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'shopEdit'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\ProfileController::class, 'shopUpdate'])->name('profile.update');

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/invoices', function() { return 'Invoices UI Pending'; })->name('invoices.index');
    
    Route::resource('/staff', App\Http\Controllers\StaffController::class)->except(['show']);
    Route::post('/staff/{staff}/generate-otp', [App\Http\Controllers\StaffController::class, 'generateOtp'])->name('staff.generate-otp');
    Route::post('/staff/{staff}/force-offline', [App\Http\Controllers\StaffController::class, 'forceOffline'])->name('staff.force-offline');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/expenses', [App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print', [App\Http\Controllers\ReportController::class, 'print'])->name('reports.print');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/purchase-orders', [App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('purchase_orders.index');
    Route::get('/medicines', function() { return 'Medicines UI Pending'; })->name('medicines.index');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/alerts', function() { return 'Alerts UI Pending'; })->name('alerts.index');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/settings', [App\Http\Controllers\StoreSettingController::class, 'index'])->name('settings.index');
    Route::get('/settings/print', [App\Http\Controllers\InvoiceSettingController::class, 'index'])->name('settings.print');
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

// Staff Authentication Routes
Route::get('/staff/login', [App\Http\Controllers\StaffAuthController::class, 'showLoginForm'])->name('staff.login');
Route::post('/staff/login', [App\Http\Controllers\StaffAuthController::class, 'login'])->name('staff.login.post');
Route::post('/staff/logout', [App\Http\Controllers\StaffAuthController::class, 'logout'])->name('staff.logout');

require __DIR__.'/auth.php';
