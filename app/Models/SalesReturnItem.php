<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    protected $fillable = [
        'sales_return_id',
        'invoice_item_id',
        'product_id',
        'product_name',
        'returned_quantity',
        'unit_price',
        'line_total',
    ];
}