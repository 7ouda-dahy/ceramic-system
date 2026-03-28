<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransfer extends Model
{
    protected $fillable = [
        'from_cashbox_id',
        'to_cashbox_id',
        'amount',
        'notes',
        'reference_code',
        'created_by',
    ];

    public function fromCashbox()
    {
        return $this->belongsTo(Cashbox::class, 'from_cashbox_id');
    }

    public function toCashbox()
    {
        return $this->belongsTo(Cashbox::class, 'to_cashbox_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}