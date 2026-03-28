<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $fillable = [
        'invoice_id',
        'total_amount',
        'refund_amount',
        'notes',
    ];

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}