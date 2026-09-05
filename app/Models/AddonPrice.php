<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddonPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'addon_id',
        'variation_id',
        'price'
    ];

    public function addon()
    {
        return $this->belongsTo(ProductAddon::class, 'addon_id');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }
}
