<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPaymentItem extends Model
{
    protected $fillable = [
        'supplier_payment_id',
        'purchase_invoice_id',
        'amount',
        'remaining_before',
        'remaining_after',
    ];

    public function supplierPayment()
    {
        return $this->belongsTo(SupplierPayment::class);
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }
}