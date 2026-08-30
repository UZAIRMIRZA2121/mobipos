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
        'logo',
        'logo_size',
        'barcode_print',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
