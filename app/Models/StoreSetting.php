<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_type',
        'discount',
        'tax',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
