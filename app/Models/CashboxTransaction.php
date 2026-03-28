<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashboxTransaction extends Model
{
    protected $fillable = [
        'cashbox_id',
        'created_by',
        'invoice_id',
        'type',
        'amount',
        'reason',
        'reference_code',
    ];

    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}