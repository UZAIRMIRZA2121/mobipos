<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (app()->environment('local')) {
        return redirect()->route('login');
    }
    return view('welcome');
});

Route::get('/auto-login/{id}', function ($id) {
    $user = \App\Models\User::findOrFail($id);
    auth()->login($user);
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    if (auth()->user()->type === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('shop.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/reports', function () {
    return redirect()->route('shop.reports.index');
})->middleware(['auth', 'verified']);

Route::post('/api/settings/backup/import-public', [App\Http\Controllers\BackupController::class, 'importPublic'])->name('api.settings.backup.import_public');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        $stores = \App\Models\User::where('type', 'shop')->orderBy('id', 'desc')->get();
        return view('admin.dashboard', [
            'totalSales' => 0,
            'totalCommissionPaid' => 0,
            'totalCommissionPending' => 0,
            'totalUsers' => 0,
            'totalStores' => $stores->count(),
            'totalSellers' => 0,
            'stores' => $stores,
        ]);
    })->name('dashboard');

    Route::post('/users/{user}/toggle-status', function(\App\Models\User $user) {
        $user->status = $user->status == 1 ? 0 : 1;
        $user->save();
        return back()->with('success', 'User status updated successfully.');
    })->name('users.toggle-status');

    Route::post('/users/{user}/impersonate', function(\App\Models\User $user) {
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect()->route('dashboard');
    })->name('users.impersonate');

    Route::get('/users/{user}/whatsapp-settings', function(\App\Models\User $user) {
        $settings = \App\Models\StoreSetting::firstOrCreate(['user_id' => $user->id]);
        return response()->json([
            'whatsapp_config' => (bool)$settings->whatsapp_config,
            'ultramsg_api_url' => $settings->ultramsg_api_url,
            'ultramsg_instance_id' => $settings->ultramsg_instance_id,
            'ultramsg_token' => $settings->ultramsg_token,
            'ultramsg_total_sent' => $settings->ultramsg_total_sent,
            'ultramsg_msg_cost' => $settings->ultramsg_msg_cost,
        ]);
    })->name('users.whatsapp.get');

    Route::post('/users/{user}/whatsapp-settings', function(\Illuminate\Http\Request $request, \App\Models\User $user) {
        $request->validate([
            'whatsapp_config' => 'nullable|boolean',
            'ultramsg_api_url' => 'nullable|string',
            'ultramsg_instance_id' => 'nullable|string',
            'ultramsg_token' => 'nullable|string',
            'ultramsg_msg_cost' => 'nullable|numeric|min:0',
        ]);
        $settings = \App\Models\StoreSetting::firstOrCreate(['user_id' => $user->id]);
        if ($request->has('whatsapp_config')) {
            $settings->whatsapp_config = $request->whatsapp_config;
        }
        $settings->ultramsg_api_url = $request->ultramsg_api_url;
        $settings->ultramsg_instance_id = $request->ultramsg_instance_id;
        $settings->ultramsg_token = $request->ultramsg_token;
        if ($request->has('ultramsg_msg_cost')) {
            $settings->ultramsg_msg_cost = $request->ultramsg_msg_cost;
        }
        $settings->save();
        return response()->json(['message' => 'Settings updated successfully']);
    })->name('users.whatsapp.update');

    Route::post('/users/{user}/whatsapp-settings/reset', function(\App\Models\User $user) {
        $settings = \App\Models\StoreSetting::firstOrCreate(['user_id' => $user->id]);
        $settings->ultramsg_total_sent = 0;
        $settings->save();
        return response()->json(['message' => 'Counter reset successfully']);
    })->name('users.whatsapp.reset');

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
    Route::get('/api/products/{product}/sales', [ProductController::class, 'salesHistory'])->name('api.products.sales');
    Route::post('/api/products/{product}/loss', [ProductController::class, 'reportLoss'])->name('api.products.loss');
    Route::get('/api/barcode-lookup/{barcode}', [ProductController::class, 'barcodeLookup'])->name('api.barcode.lookup');

    Route::get('/api/categories', [CategoryController::class, 'apiIndex'])->name('api.categories.index');
    Route::post('/api/categories', [CategoryController::class, 'store'])->name('api.categories.store');
    Route::put('/api/categories/{category}', [CategoryController::class, 'update'])->name('api.categories.update');
    Route::delete('/api/categories/{category}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');

    Route::get('/api/customers', [CustomerController::class, 'apiIndex'])->name('api.customers.index');
    Route::post('/api/customers', [CustomerController::class, 'store'])->name('api.customers.store');
    Route::post('/api/customers/{customer}', [CustomerController::class, 'update'])->name('api.customers.update'); // POST instead of PUT because of FormData file uploads
    Route::delete('/api/customers/{customer}', [CustomerController::class, 'destroy'])->name('api.customers.destroy');
    Route::delete('/api/customers/{customer}/agreements-images/{index}', [CustomerController::class, 'deleteAgreementImage'])->name('api.customers.delete_image');
    Route::delete('/api/customers/{customer}/cnic-image/{type}', [CustomerController::class, 'deleteCnicImage'])->name('api.customers.delete_cnic_image');

    // Expenses
    Route::get('/api/expenses', [App\Http\Controllers\ExpenseController::class, 'apiIndex']);
    Route::post('/api/expenses', [App\Http\Controllers\ExpenseController::class, 'store']);
    Route::put('/api/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'update']);
    Route::delete('/api/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'destroy']);

    // Customer Ledger
    Route::get('/api/customers/{customer}/ledger', [App\Http\Controllers\CustomerLedgerController::class, 'apiIndex']);
    Route::post('/api/customers/{customer}/ledger', [App\Http\Controllers\CustomerLedgerController::class, 'store']);
    Route::delete('/api/customers/{customer}/ledger/{ledger}', [App\Http\Controllers\CustomerLedgerController::class, 'destroy']);
    Route::get('/customers/{customer}/print-ledger', [App\Http\Controllers\CustomerLedgerController::class, 'printLedger'])->name('customers.print_ledger');

    Route::get('/api/settings', [App\Http\Controllers\StoreSettingController::class, 'apiGet'])->name('api.settings.get');
    Route::post('/api/settings', [App\Http\Controllers\StoreSettingController::class, 'apiUpdate'])->name('api.settings.update');

    Route::get('/api/settings/backup/export', [App\Http\Controllers\BackupController::class, 'export'])->name('api.settings.backup.export');
    Route::post('/api/settings/backup/import', [App\Http\Controllers\BackupController::class, 'import'])->name('api.settings.backup.import');

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

    // Installments
    Route::get('/installments', [App\Http\Controllers\InstallmentController::class, 'index'])->name('installments.index');
    Route::get('/installments/{id}', [App\Http\Controllers\InstallmentController::class, 'show'])->name('installments.show');
    Route::get('/installments/{id}/print', [App\Http\Controllers\InstallmentController::class, 'print'])->name('installments.print');
    Route::post('/installments/{id}/payment', [App\Http\Controllers\InstallmentController::class, 'addPayment'])->name('installments.addPayment');
    Route::put('/installments/{id}', [App\Http\Controllers\InstallmentController::class, 'update'])->name('installments.update');
    Route::put('/installments/payment/{paymentId}', [App\Http\Controllers\InstallmentController::class, 'updatePayment'])->name('installments.updatePayment');
    Route::delete('/installments/payment/{paymentId}', [App\Http\Controllers\InstallmentController::class, 'destroyPayment'])->name('installments.destroyPayment');
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
Route::get('/trial-ended', [\App\Http\Controllers\TrialController::class, 'show'])->name('trial.ended');
Route::post('/trial-ended/verify', [\App\Http\Controllers\TrialController::class, 'verify'])->name('trial.verify');
Route::post('/trial-ended/resend', [\App\Http\Controllers\TrialController::class, 'resend'])->name('trial.resend');

Route::get('/staff/login', [App\Http\Controllers\StaffAuthController::class, 'showLoginForm'])->name('staff.login');
Route::post('/staff/login', [App\Http\Controllers\StaffAuthController::class, 'login'])->name('staff.login.post');
Route::post('/staff/logout', [App\Http\Controllers\StaffAuthController::class, 'logout'])->name('staff.logout');

require __DIR__.'/auth.php';
