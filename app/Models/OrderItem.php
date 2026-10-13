<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'seller_id',
        'qty',
        'buy_price',
        'sell_price',
        'user_id',
        'imeis',
        'meta_data',
        'is_refunded',
        'refunded_at',
    ];

    protected $casts = [
        'meta_data' => 'array',
        'is_refunded' => 'boolean',
        'refunded_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function seller()
    {
        return $this->belongsTo(Customer::class, 'seller_id');
    }

    public function stockUnits()
    {
        return $this->hasMany(ProductStockUnit::class, 'order_item_id');
    }
}
