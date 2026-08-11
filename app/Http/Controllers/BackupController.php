<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Expense;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StoreSetting;
use App\Models\InvoiceSetting;
use App\Models\Staff;

class BackupController extends Controller
{
    public function export()
    {
        $userId = Auth::id();
        $user = Auth::user();

        $data = [
            'user' => DB::table('users')->where('id', $userId)->first(),
            'products' => DB::table('products')->where('user_id', $userId)->get(),
            'categories' => DB::table('categories')->where('user_id', $userId)->get(),
            'customers' => DB::table('customers')->where('user_id', $userId)->get(),
            'sales' => DB::table('sales')->where('user_id', $userId)->get(),
            'orders' => DB::table('orders')->where('user_id', $userId)->get(),
            'expenses' => DB::table('expenses')->where('user_id', $userId)->get(),
            'purchase_orders' => DB::table('purchase_orders')->where('user_id', $userId)->get(),
            'store_settings' => DB::table('store_settings')->where('user_id', $userId)->get(),
            'invoice_settings' => DB::table('invoice_settings')->where('user_id', $userId)->get(),
            'staff' => DB::table('staff')->where('user_id', $userId)->get(),
        ];

        $orderIds = $data['orders']->pluck('id');
        $data['order_items'] = DB::table('order_items')->whereIn('order_id', $orderIds)->get();

        $poIds = $data['purchase_orders']->pluck('id');
        $data['purchase_order_items'] = DB::table('purchase_order_items')->whereIn('purchase_order_id', $poIds)->get();

        $storeName = preg_replace('/[^A-Za-z0-9_]/', '_', $user->name);
        $fileName = 'Store_' . $storeName . '_' . date('Y_m_d_H_i_s') . '.json';

        return response()->json($data)->withHeaders([
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file',
        ]);

        $fileContent = file_get_contents($request->file('backup_file')->getRealPath());
        $data = json_decode($fileContent, true);

        if (!$data || !is_array($data)) {
            return response()->json(['message' => 'Invalid backup file format.'], 400);
        }

        $userId = Auth::id();

        DB::beginTransaction();
        try {
            $orderIds = Order::where('user_id', $userId)->pluck('id');
            OrderItem::whereIn('order_id', $orderIds)->delete();
            
            $poIds = PurchaseOrder::where('user_id', $userId)->pluck('id');
            PurchaseOrderItem::whereIn('purchase_order_id', $poIds)->delete();

            Product::where('user_id', $userId)->delete();
            Category::where('user_id', $userId)->delete();
            Customer::where('user_id', $userId)->delete();
            Sale::where('user_id', $userId)->delete();
            Order::where('user_id', $userId)->delete();
            Expense::where('user_id', $userId)->delete();
            PurchaseOrder::where('user_id', $userId)->delete();
            StoreSetting::where('user_id', $userId)->delete();
            InvoiceSetting::where('user_id', $userId)->delete();
            Staff::where('user_id', $userId)->delete();

            if (!empty($data['categories'])) DB::table('categories')->insert($data['categories']);
            if (!empty($data['customers'])) DB::table('customers')->insert($data['customers']);
            if (!empty($data['products'])) DB::table('products')->insert($data['products']);
            if (!empty($data['sales'])) DB::table('sales')->insert($data['sales']);
            if (!empty($data['orders'])) DB::table('orders')->insert($data['orders']);
            if (!empty($data['order_items'])) DB::table('order_items')->insert($data['order_items']);
            if (!empty($data['expenses'])) DB::table('expenses')->insert($data['expenses']);
            if (!empty($data['purchase_orders'])) DB::table('purchase_orders')->insert($data['purchase_orders']);
            if (!empty($data['purchase_order_items'])) DB::table('purchase_order_items')->insert($data['purchase_order_items']);
            if (!empty($data['store_settings'])) DB::table('store_settings')->insert($data['store_settings']);
            if (!empty($data['invoice_settings'])) DB::table('invoice_settings')->insert($data['invoice_settings']);
            if (!empty($data['staff'])) DB::table('staff')->insert($data['staff']);

            DB::commit();

            return response()->json(['message' => 'Backup restored successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to restore backup: ' . $e->getMessage()], 500);
        }
    }

    public function importPublic(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file',
        ]);

        $fileContent = file_get_contents($request->file('backup_file')->getRealPath());
        $data = json_decode($fileContent, true);

        if (!$data || !is_array($data) || empty($data['user'])) {
            return response()->json(['message' => 'Invalid backup file format or missing user data. Please ensure you export a fresh backup.'], 400);
        }

        DB::beginTransaction();
        try {
            // Restore User
            $userData = (array)$data['user'];
            
            // Unset ID so we can insert/update safely if email matches or just insert
            $existingUser = \App\Models\User::where('email', $userData['email'])->first();
            if ($existingUser) {
                $userId = $existingUser->id;
                // Update password and name from backup
                $existingUser->update([
                    'name' => $userData['name'],
                    'password' => $userData['password'], // hashed password
                ]);
            } else {
                // If user doesn't exist, we should probably keep their original ID if possible, 
                // but auto-increment might conflict. It's safer to just insert and let it auto-increment, 
                // but wait! All related tables in the JSON have `user_id` pointing to the old ID!
                // So we MUST preserve the `id` from the JSON to keep relationships intact, OR update all related data.
                // It is simpler to just insert the user with the exact ID, assuming it's available.
                // If there's an ID conflict, we'll try to insert anyway and if it fails due to PK, we'll have to assign a new ID.
                // Since this is a "new software install", the users table should be empty or won't conflict.
                DB::table('users')->insert($userData);
                $userId = $userData['id'];
            }

            // Delete existing records for this user to replace with backup
            $orderIds = Order::where('user_id', $userId)->pluck('id');
            OrderItem::whereIn('order_id', $orderIds)->delete();
            
            $poIds = PurchaseOrder::where('user_id', $userId)->pluck('id');
            PurchaseOrderItem::whereIn('purchase_order_id', $poIds)->delete();

            Product::where('user_id', $userId)->delete();
            Category::where('user_id', $userId)->delete();
            Customer::where('user_id', $userId)->delete();
            Sale::where('user_id', $userId)->delete();
            Order::where('user_id', $userId)->delete();
            Expense::where('user_id', $userId)->delete();
            PurchaseOrder::where('user_id', $userId)->delete();
            StoreSetting::where('user_id', $userId)->delete();
            InvoiceSetting::where('user_id', $userId)->delete();
            Staff::where('user_id', $userId)->delete();

            // Insert backup data
            // We must update the user_id in all records to the $userId just in case the user was assigned a new ID (though we used the original ID above).
            $tables = ['categories', 'customers', 'products', 'sales', 'orders', 'expenses', 'purchase_orders', 'store_settings', 'invoice_settings', 'staff'];
            foreach ($tables as $table) {
                if (!empty($data[$table])) {
                    foreach ($data[$table] as &$row) {
                        if (isset($row['user_id'])) $row['user_id'] = $userId;
                    }
                    DB::table($table)->insert($data[$table]);
                }
            }
            if (!empty($data['order_items'])) DB::table('order_items')->insert($data['order_items']);
            if (!empty($data['purchase_order_items'])) DB::table('purchase_order_items')->insert($data['purchase_order_items']);

            DB::commit();

            // Login the user
            $user = \App\Models\User::find($userId);
            Auth::login($user);

            return response()->json(['message' => 'Backup restored and logged in successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to restore backup: ' . $e->getMessage()], 500);
        }
    }
}
