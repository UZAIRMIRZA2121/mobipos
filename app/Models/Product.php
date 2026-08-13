<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'type',
        'condition',
        'code',
        'barcode',
        'imei_serial',
        'color',
        'storage',
        'image',
        'purchase_price',
        'sale_price',
        'status',
        'stock',
        'category_id',
        'buyer_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function buyer()
    {
        return $this->belongsTo(Customer::class, 'buyer_id');
    }

    public function stockUnits()
    {
        return $this->hasMany(ProductStockUnit::class);
    }
}
