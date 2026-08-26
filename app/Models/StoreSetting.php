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
        'ultramsg_api_url',
        'ultramsg_instance_id',
        'ultramsg_token',
        'ultramsg_total_sent',
        'ultramsg_msg_cost',
        'whatsapp_config',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
