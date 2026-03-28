<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteRecord extends Model
{
    protected $fillable = [
        'product_id',
        'branch_id',
        'created_by',
        'product_name',
        'quantity_meter',
        'unit_cost',
        'total_cost',
        'reason',
        'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}