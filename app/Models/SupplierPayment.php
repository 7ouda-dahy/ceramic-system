<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = [
        'supplier_id',
        'cashbox_id',
        'created_by',
        'total_amount',
        'notes',
        'reference_code',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierPaymentItem::class);
    }
}