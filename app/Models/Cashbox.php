<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashbox extends Model
{
    protected $fillable = ['branch_id', 'name', 'is_central', 'is_active'];

    protected $casts = [
        'is_central' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactions()
    {
        return $this->hasMany(CashboxTransaction::class);
    }

    public function getBalanceAttribute(): float
    {
        $in = $this->transactions()->where('type', 'IN')->sum('amount');
        $out = $this->transactions()->where('type', 'OUT')->sum('amount');
        return (float) ($in - $out);
    }
}