<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStockUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'imeis',
        'is_pta',
        'status',
        'order_item_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
