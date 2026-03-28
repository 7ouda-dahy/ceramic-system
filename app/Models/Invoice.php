<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'branch_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'total_amount',
        'discount_value',
        'discount_reason',
        'paid_amount',
        'remaining_amount',
        'payment_status',
        'created_by'
    ];

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }
}