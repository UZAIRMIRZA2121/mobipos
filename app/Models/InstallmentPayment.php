<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    protected $fillable = [
        'installment_id',
        'amount',
        'payment_date',
        'payment_method',
        'notes',
    ];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }
}
