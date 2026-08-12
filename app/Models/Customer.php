<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'user_id',
        'cnic_number',
        'cnic_front',
        'cnic_back',
        'agreements_images',
    ];

    protected $casts = [
        'agreements_images' => 'array',
    ];

    protected $appends = ['balance'];

    public function getBalanceAttribute()
    {
        $lastLedger = $this->ledgers()->orderBy('date', 'desc')->orderBy('id', 'desc')->first();
        return $lastLedger ? (float) $lastLedger->balance : 0;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function ledgers()
    {
        return $this->hasMany(CustomerLedger::class);
    }
}
