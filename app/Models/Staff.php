<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'otp',
        'status',
        'staff_type',
        'privileges',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
