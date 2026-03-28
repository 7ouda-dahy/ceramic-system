<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $fillable = [
        'supplier_id',
        'cashbox_id',
        'created_by',
        'supplier_name',
        'supplier_phone',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'payment_status',
    ];
}