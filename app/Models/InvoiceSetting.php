<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'header_text',
        'address',
        'phone',
        'footer_text',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
