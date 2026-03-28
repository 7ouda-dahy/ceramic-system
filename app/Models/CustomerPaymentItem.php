<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPaymentItem extends Model
{
    protected $fillable = [
        'customer_payment_id',
        'invoice_id',
        'cashbox_id',
        'amount',
        'remaining_before',
        'remaining_after',
    ];

    public function customerPayment()
    {
        return $this->belongsTo(CustomerPayment::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class);
    }
}